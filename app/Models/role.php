<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
class role extends Model
{
    

    public function get_permission(){
        $access_array = array(
            "0" => "",
            "1" => "CRUD",
            "2" => "RU",
            "3" => "R",
            
        );
        $permission = DB::connection("intra_payroll")->table("tbl_role_access")
            ->where("id", Auth::user()->role_id)
            ->first();
            $access = array();
            if($permission != null){
                    $explode = explode(";",$permission->permission);

                    foreach($explode as $perm){
                        $perm_explode = explode("|", $perm);
                        $lib= DB::connection("intra_payroll")->table("lib_permission")
                            ->where("id", $perm_explode[0])
                            ->first();

                       
                        if($lib != null){
                            if(!isset($access[$lib->name])){
                                $access[$lib->route] = array(  
                                "icon" => $lib->icon,
                                "name" => $lib->name,
                                "route" => $lib->route,
                                "access"=> $access_array[$perm_explode[1]],
                                "user_type" => $permission->type
                                );

                            }
                        }
                    }

            }


          
    return $access;
    }






}
