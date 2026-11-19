<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Database;
use PDO;

final class OrientationTest
{
    public static function activeQuestions(): array
    {
        $sql = "SELECT q.id question_id,q.question_text,q.help_text,q.position,q.response_mode,q.is_required,o.id option_id,o.option_text,o.position option_position
                FROM questions q LEFT JOIN answer_options o ON o.question_id=q.id AND o.is_active=1
                WHERE q.is_active=1 ORDER BY q.position,o.position";
        $rows=Database::connection()->query($sql)->fetchAll(); $questions=[];
        foreach($rows as $row){$id=(int)$row['question_id']; if(!isset($questions[$id])){$questions[$id]=['id'=>$id,'text'=>$row['question_text'],'help'=>$row['help_text'],'position'=>(int)$row['position'],'mode'=>$row['response_mode'],'required'=>(bool)$row['is_required'],'options'=>[]];} if($row['option_id']!==null)$questions[$id]['options'][]=['id'=>(int)$row['option_id'],'text'=>$row['option_text']];}
        return array_values(array_filter($questions,fn($q)=>count($q['options'])>0));
    }
    public static function currentOrCreate(int $userId): int
    {
        $db=Database::connection();$expire=$db->prepare("UPDATE orientation_tests SET status='abandoned' WHERE user_id=? AND status='in_progress' AND started_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");$expire->execute([$userId]);$s=$db->prepare("SELECT id FROM orientation_tests WHERE user_id=? AND status='in_progress' ORDER BY id DESC LIMIT 1");$s->execute([$userId]);$id=$s->fetchColumn();if($id)return(int)$id;
        $s=$db->prepare("INSERT INTO orientation_tests(user_id,status,question_count) VALUES(?,'in_progress',?)");$s->execute([$userId,count(self::activeQuestions())]);return(int)$db->lastInsertId();
    }
    public static function currentInProgress(int $userId): ?int
    {$db=Database::connection();$s=$db->prepare("UPDATE orientation_tests SET status='abandoned' WHERE user_id=? AND status='in_progress' AND started_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");$s->execute([$userId]);$s=$db->prepare("SELECT id FROM orientation_tests WHERE user_id=? AND status='in_progress' ORDER BY id DESC LIMIT 1");$s->execute([$userId]);$id=$s->fetchColumn();return$id?(int)$id:null;}
    public static function saveAnswer(int $testId,int $userId,int $questionId,int $optionId):void
    {$db=Database::connection();$db->beginTransaction();try{$s=$db->prepare("SELECT id FROM orientation_tests WHERE id=? AND user_id=? AND status='in_progress' AND started_at>=DATE_SUB(NOW(),INTERVAL 7 DAY) FOR UPDATE");$s->execute([$testId,$userId]);if(!$s->fetchColumn())throw new \RuntimeException('Cette tentative est expirée ou inaccessible.');$s=$db->prepare('SELECT q.question_text,o.option_text FROM questions q JOIN answer_options o ON o.question_id=q.id WHERE q.id=? AND o.id=? AND q.is_active=1 AND o.is_active=1 AND q.response_mode=\'single\'');$s->execute([$questionId,$optionId]);$row=$s->fetch();if(!$row)throw new \RuntimeException('Cette réponse ne correspond pas à la question.');$s=$db->prepare('DELETE FROM test_answers WHERE test_id=? AND question_id=?');$s->execute([$testId,$questionId]);$s=$db->prepare('INSERT INTO test_answers(test_id,question_id,option_id,question_text_snapshot,option_text_snapshot) VALUES(?,?,?,?,?)');$s->execute([$testId,$questionId,$optionId,$row['question_text'],$row['option_text']]);$answerId=(int)$db->lastInsertId();$s=$db->prepare('INSERT INTO test_answer_score_snapshots(test_answer_id,domain_id,points) SELECT ?,domain_id,points FROM option_domain_scores WHERE option_id=?');$s->execute([$answerId,$optionId]);$db->commit();}catch(\Throwable $e){if($db->inTransaction())$db->rollBack();throw$e;}}
    public static function latestCompleted(int $userId): ?array
    {$s=Database::connection()->prepare("SELECT id,completed_at,question_count FROM orientation_tests WHERE user_id=? AND status='completed' ORDER BY completed_at DESC,id DESC LIMIT 1");$s->execute([$userId]);return $s->fetch()?:null;}
    public static function savedAnswers(int $testId): array
    {$s=Database::connection()->prepare('SELECT question_id,option_id FROM test_answers WHERE test_id=?');$s->execute([$testId]);$out=[];foreach($s->fetchAll() as $r)$out[(int)$r['question_id']][]=(int)$r['option_id'];return$out;}
    public static function saveAndComplete(int $testId,int $userId,array $questions,array $answers): void
    {
        $db=Database::connection();$db->beginTransaction();try{
            $check=$db->prepare("SELECT status FROM orientation_tests WHERE id=? AND user_id=? FOR UPDATE");$check->execute([$testId,$userId]);if($check->fetchColumn()!=='in_progress')throw new \RuntimeException('Ce test n’est plus modifiable.');
            $del=$db->prepare('DELETE FROM test_answers WHERE test_id=?');$del->execute([$testId]);$insert=$db->prepare('INSERT INTO test_answers(test_id,question_id,option_id,question_text_snapshot,option_text_snapshot) VALUES(?,?,?,?,?)');$snap=$db->prepare('INSERT INTO test_answer_score_snapshots(test_answer_id,domain_id,points) SELECT ?,domain_id,points FROM option_domain_scores WHERE option_id=?');
            foreach($questions as $q)foreach($answers[$q['id']]??[] as $oid){$option=null;foreach($q['options'] as $o)if($o['id']===$oid)$option=$o;if(!$option)continue;$insert->execute([$testId,$q['id'],$oid,$q['text'],$option['text']]);$snap->execute([(int)$db->lastInsertId(),$oid]);}
            $score=$db->prepare('SELECT s.domain_id,SUM(s.points) score FROM test_answer_score_snapshots s JOIN test_answers a ON a.id=s.test_answer_id WHERE a.test_id=? GROUP BY s.domain_id');$score->execute([$testId]);$scores=$score->fetchAll();$earned=[];foreach($scores as $r)$earned[(int)$r['domain_id']]=(float)$r['score'];
            $maxStmt=$db->query('SELECT domain_id,SUM(max_points) maximum FROM (SELECT ods.domain_id,o.question_id,MAX(ods.points) max_points FROM answer_options o JOIN option_domain_scores ods ON ods.option_id=o.id JOIN questions q ON q.id=o.question_id AND q.is_active=1 WHERE o.is_active=1 GROUP BY ods.domain_id,o.question_id) domain_question_max GROUP BY domain_id');$maximum=[];foreach($maxStmt->fetchAll() as $r)$maximum[(int)$r['domain_id']]=(float)$r['maximum'];
            $domains=$db->query('SELECT id,name FROM domains WHERE is_active=1')->fetchAll();$ranked=[];foreach($domains as $d){$domain=(int)$d['id'];$points=$earned[$domain]??0.0;$max=$maximum[$domain]??0.0;$ranked[]=['id'=>$domain,'name'=>$d['name'],'score'=>$points,'percent'=>$max>0?round($points/$max*100,2):0.0];}usort($ranked,fn($a,$b)=>($b['percent']<=>$a['percent'])?: (($b['score']<=>$a['score']) ?: strcmp($a['name'],$b['name'])));$insertResult=$db->prepare('INSERT INTO test_domain_results(test_id,domain_id,score,percentage,rank_position,is_recommended,explanation) VALUES(?,?,?,?,?,?,?)');foreach($ranked as $i=>$r){$insertResult->execute([$testId,$r['id'],$r['score'],$r['percent'],$i+1,$r['percent']>=30?1:0,'Score relatif au maximum atteignable dans ce domaine.']);}
            $upd=$db->prepare("UPDATE orientation_tests SET status='completed',completed_at=NOW(),question_count=?,scoring_method='share_of_maximum' WHERE id=?");$upd->execute([count($questions),$testId]);$db->commit();
        }catch(\Throwable $e){if($db->inTransaction())$db->rollBack();throw$e;}
    }
    public static function results(int $testId):array
    {$db=Database::connection();$s=$db->prepare('SELECT d.name,d.description,r.score,r.percentage,r.rank_position,r.is_recommended FROM test_domain_results r JOIN domains d ON d.id=r.domain_id WHERE r.test_id=? AND r.is_recommended=1 ORDER BY r.percentage DESC,r.score DESC,d.name ASC LIMIT 5');$s->execute([$testId]);$rows=$s->fetchAll();if($rows)return$rows;$s=$db->prepare('SELECT d.name,d.description,r.score,r.percentage,r.rank_position,r.is_recommended FROM test_domain_results r JOIN domains d ON d.id=r.domain_id WHERE r.test_id=? ORDER BY r.percentage DESC,r.score DESC,d.name ASC LIMIT 3');$s->execute([$testId]);return$s->fetchAll();}
}



