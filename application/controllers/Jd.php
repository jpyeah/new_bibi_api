<?php
/**
 * Created by PhpStorm.
 * User: jp
 * Date: 16/4/14
 * Time: 14:51
 */
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
class JdController extends ApiYafControllerAbstract {


  public function getjsonAction(){
         exit;
         $pdo = new PdoDb;
         $url='http://localhost:9089/json/25.json';
         $html=file_get_contents($url);
         $data=json_decode($html,true);

         
         foreach($data['data'] as $k =>$val){
                $parent=26;
                $ata['name']=$val['name'];
                $ata['image']=$val['icon'];
                $ata['image_small']=$val['icon'];
                $ata['parent_id']=$parent;
                $ata['sort']=50;
                $ata['created_by']=10;
                $ata['updated_by']=10;
                $ata['updated_at']=date('Ymd',time());
                $ata['created_at']=date('Ymd',time());
                $ata['short_description']=$val['name'];
                $ata['app_featured_home']=$parent;
                $ata['app_featured_home_sort']=99;
                $ata['parent_ids']='1,'.$parent;
                $ata['image_medium']=$val['icon'];
                $ata['image_large']=$val['icon'];
                $ata['status']=1;
                //print_r($ata);

                $id=$pdo->insert('product_category',$ata);

                if($id){
                    
                       foreach($val['catelogyList'] as $j =>$list){
                              
                              $parented=$id;
                              $atar['name']=$list['name'];
                              $atar['image']=$list['icon'];
                              $atar['image_small']=$list['icon'];
                              $atar['parent_id']=$parented;
                              $atar['sort']=50;
                              $atar['created_by']=10;
                              $atar['updated_by']=10;
                              $atar['updated_at']=date('Ymd',time());
                              $atar['created_at']=date('Ymd',time());
                              $atar['short_description']=$list['name'];
                              $atar['app_featured_home']=$parent;
                              $atar['app_featured_home_sort']=99;
                              $atar['parent_ids']='1,'.$parent.','.$parented;
                              $atar['image_medium']=$list['icon'];
                              $atar['image_large']=$list['icon'];
                              if(@$list['searchKey']){
                                 $atar['meta_keywords']=$list['searchKey'];
                              }

                              $atar['status']=1;
                              $res=$pdo->insert('product_category',$atar);

                              //print_r($res);
                       }

                }
            /*    foreach($val['catelogyList'] as $j =>$list){
                        

                        print_r($list);
                }
             */
         }

  }


  public function getcatAction(){
          exit;
          $pdo = new PdoDb;
          $sql='select id,parent_ids from product_category';
          $list=$pdo->query($sql);

          foreach($list as $k =>$val){
                 $num=count(explode(",",$val['parent_ids']));
                 if($num==1){
                    $type=1;
                 }else if($num==2){
                    $type=2;
                 }else if($num==3){
                    $type=3;
                 }
                 $sql='update product_category set type = '.$type.' where id ='.$val['id'];
                // print_r($sql).'</br>';

                 $pdo-> execute($sql);
          }
  }

  public function changeimgAction(){
         
         $pdo=new PdoDb;
         $sql='select id,image,image_small from product_category';
         $list=$pdo->query($sql);

         foreach($list as $k =>$val){
                $url=$val['image_small'];
                $url=str_replace('.webp','',$url);
                
               // $sql='update product_category set image = '."'".$url."'".',image_small ='."'".$url."'".' where id = '.$val['id'];

                //$pdo->query($sql);
               

         }

  } 


}