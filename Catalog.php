<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Database;

final class Catalog
{
    public const TYPES=['domaines','formations','etablissements','metiers'];
    private const TABLES=['domaines'=>'domains','formations'=>'programs','etablissements'=>'institutions','metiers'=>'careers'];
    private const FIELDS=[
      'domaines'=>['name','slug','description','icon_key','is_active'],
      'formations'=>['domain_id','name','slug','description','admission_requirements','duration','degree_level','is_active'],
      'etablissements'=>['name','description','institution_type','address','city','department','website','phone','email','is_active'],
      'metiers'=>['domain_id','name','description','opportunities','is_active']
    ];
    public static function all(string $type):array{$table=self::table($type);$order=$type==='domaines'?'name':($type==='formations'?'name':($type==='metiers'?'name':'name'));$s=Database::connection()->query("SELECT * FROM {$table} ORDER BY is_active DESC,{$order}");return$s->fetchAll();}
    public static function find(string $type,int $id):?array{$s=Database::connection()->prepare('SELECT * FROM '.self::table($type).' WHERE id=?');$s->execute([$id]);return$s->fetch()?:null;}
    public static function choices():array{$db=Database::connection();return['domaines'=>$db->query('SELECT id,name FROM domains ORDER BY name')->fetchAll(),'etablissements'=>$db->query('SELECT id,name FROM institutions ORDER BY name')->fetchAll(),'formations'=>$db->query('SELECT id,name FROM programs ORDER BY name')->fetchAll(),'metiers'=>$db->query('SELECT id,name FROM careers ORDER BY name')->fetchAll(),'series'=>$db->query('SELECT id,code,name FROM bac_series WHERE is_active=1 ORDER BY code')->fetchAll()];}
    public static function save(string $type,?int $id,array $values,array $relations):void{$db=Database::connection();$table=self::table($type);$fields=self::FIELDS[$type];$db->beginTransaction();try{if($id){$sets=implode(',',array_map(fn($f)=>"`$f`=?",$fields));$stmt=$db->prepare("UPDATE {$table} SET {$sets} WHERE id=?");$stmt->execute([...array_map(fn($f)=>$values[$f]??null,$fields),$id]);}else{$cols=implode(',',array_map(fn($f)=>"`$f`",$fields));$marks=implode(',',array_fill(0,count($fields),'?'));$stmt=$db->prepare("INSERT INTO {$table} ({$cols}) VALUES ({$marks})");$stmt->execute(array_map(fn($f)=>$values[$f]??null,$fields));$id=(int)$db->lastInsertId();}
      if($type==='formations')self::sync('program_institutions','program_id','institution_id',$id,$relations['institution_ids']??[]);
      if($type==='formations')self::sync('program_careers','program_id','career_id',$id,$relations['career_ids']??[]);
      if($type==='formations')self::syncBacSeries($id,$relations['bac_series_ids']??[]);
      if($type==='etablissements')self::sync('program_institutions','institution_id','program_id',$id,$relations['program_ids']??[]);
      if($type==='metiers')self::sync('program_careers','career_id','program_id',$id,$relations['program_ids']??[]);
      $db->commit();}catch(\Throwable $e){if($db->inTransaction())$db->rollBack();throw$e;}}
    private static function sync(string $table,string $owner,string $target,int $id,array $ids):void{$db=Database::connection();$q=$db->prepare("DELETE FROM {$table} WHERE {$owner}=?");$q->execute([$id]);$insert=$db->prepare("INSERT INTO {$table} ({$owner},{$target}) VALUES (?,?)");foreach(array_unique(array_filter(array_map('intval',$ids))) as $foreign)$insert->execute([$id,$foreign]);}
    private static function syncBacSeries(int $programId,array $ids):void{$db=Database::connection();$ids=array_values(array_unique(array_filter(array_map('intval',$ids))));$s=$db->prepare('SELECT bac_series_id FROM program_bac_series WHERE program_id=?');$s->execute([$programId]);$old=array_map('intval',$s->fetchAll(\PDO::FETCH_COLUMN));$remove=array_diff($old,$ids);if($remove){$q=$db->prepare('DELETE FROM program_bac_series WHERE program_id=? AND bac_series_id=?');foreach($remove as $sid)$q->execute([$programId,$sid]);}$add=array_diff($ids,$old);if($add){$q=$db->prepare('INSERT INTO program_bac_series(program_id,bac_series_id) VALUES (?,?)');foreach($add as $sid)$q->execute([$programId,$sid]);}}
    public static function relations(string $type,int $id):array{$db=Database::connection();$map=['formations'=>['program_institutions','program_id','institution_id','institution_ids'],'etablissements'=>['program_institutions','institution_id','program_id','program_ids'],'metiers'=>['program_careers','career_id','program_id','program_ids']];if(!isset($map[$type]))return[];[$table,$owner,$target,$key]=$map[$type];$s=$db->prepare("SELECT {$target} FROM {$table} WHERE {$owner}=?");$s->execute([$id]);$result=[$key=>array_map('intval',$s->fetchAll(\PDO::FETCH_COLUMN))];if($type==='formations'){$q=$db->prepare('SELECT bac_series_id FROM program_bac_series WHERE program_id=?');$q->execute([$id]);$result['bac_series_ids']=array_map('intval',$q->fetchAll(\PDO::FETCH_COLUMN));}return$result;}
    public static function delete(string $type,int $id):void{$db=Database::connection();$stmt=$db->prepare('DELETE FROM '.self::table($type).' WHERE id=?');$stmt->execute([$id]);}
    public static function toggle(string $type,int $id):void{$stmt=Database::connection()->prepare('UPDATE '.self::table($type).' SET is_active=IF(is_active=1,0,1) WHERE id=?');$stmt->execute([$id]);}
    public static function count(string $type):int{return(int)Database::connection()->query('SELECT COUNT(*) FROM '.self::table($type))->fetchColumn();}
    private static function table(string $type):string{if(!isset(self::TABLES[$type]))throw new \InvalidArgumentException('Type de référentiel inconnu.');return self::TABLES[$type];}
}
