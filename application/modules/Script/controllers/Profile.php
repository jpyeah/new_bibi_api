<?php
/*
*
*更新bibi_no 
*
* 
 */ 
class ProfileController extends ApiYafControllerAbstract{


    public function sendCodeAction()
    {

        $code = rand(1000,9999);

        $response = array(
            'code' => $code
        );

        Common::sendSMS('18676340510',array($code),"184610");

        $this->send($response);

    }

    //
  public function updatebibiAction(){
     
        $db=new ProfileModel();
        $sql = '
                 SELECT
                      user_id
                    FROM
                      `bibi_user_profile` 
        ';
        $data = $db->query($sql);


      foreach($data as $key => $value){
        $val=$value['user_id']+10000;
         $sql = '
                 UPDATE
                    `bibi_user_profile` 
                    set `bibi_no`='.$val.'
                    WHERE
                    `user_id`='."'".$value['user_id']."'".'
        ';
         $db->query($sql);
     }

    }
  
  public function testAction(){
       
       echo "12121";

  }
}
