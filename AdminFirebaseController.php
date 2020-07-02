<?php

namespace App\Http\Controllers\Admin;


use Kreait\Firebase\Database;
// use Kreait\Firebase\Firestore;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\MailSendController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class AdminFirebaseController extends Controller
{
    public function index()
   	{
   		$database = app('firebase.database');
   		$reference = $database->getReference('COMILLA');
   		$value = $reference->getValue();
   		dd($value);
   	}

   	public function index2()
   	{
   		$firestore = app('firebase.firestore');
   		$database = $firestore->database();
   		$collectionReference = $database->collection('doctors');
   		$documents = $collectionReference->documents();
   		// $snapshot = $documentReference->snapshot();
   		// $reference = $database->getReference('users');
   		// $value = $database->getValue();
   		// foreach ($documents as  $document) {
   		// 	if ($document->exists()){
   		// 		print_r($document->data());
   		// 	}
   		// 	print_r('----------------------------------------------------------\r\n');
   		// }
   		dd($documents);


   	}

    // Doctor Registration
    /*
    public function setdoctors()
   	{
   		$firestore = app('firebase.firestore');
   		$database = $firestore->database();
   		$doctorRef = $database->collection('doctors');
   		$doctorRef->add([
   			'active'=>false,
   			'dateOfBirth'=>'2/2/2020',
   			'district'=>'dhaka',
   			'districtId'=>1,
   			'doctorType'=>'general',
   			'email'=>'amit@gmail.com',
   			'gender'=>'Male',
   			'name'=>'amit kumar2',
   			'online'=>true,
   			'photoUrl'=>'www.photo.com',
   			'price'=>300,
   			'regNo'=>'00011',
   			'totalCount'=>2,
   			'totalRating'=>8
   		]);
   		dd('Doctor add successfully');


       }
    */

   	public function getDoctor()
   	{
   		$firestore = app('firebase.firestore');
   		$database = $firestore->database();
   		$doctorsRef = $database->collection('doctors');
   		$query = $doctorsRef->where('active','=',true);
   		$snapshot = $query->documents();
   		dd($snapshot);
   	}

   	public function editDoctor()
   	{
   		$firestore = app('firebase.firestore');
   		$database = $firestore->database();
   		$doctorRef = $database->collection('doctors')->document('AmitKumar');
   		// dd($doctorRef);
		$doctorRef->update([
   			['path' => 'active', 'value' => true],
   			['path' => 'dateOfBirth', 'value' => '20/20/2020']

		]);
   		dd('doctor update sccessfully');
   	}


   	public function districtDoctor()
   	{
   		$firestore = app('firebase.firestore');
   		$database = $firestore->database();
   		print_r("<pre>");
   		//district wise total number doctor

   		$districtRef = $database->collection('districts');
   		$districts = $districtRef->documents();
   		$dis_val = array_fill(0, 65,0);
   		foreach ($districts as $district) {
   			if ($district->exists()){
   				$temp = array('id' => $district['id'],'name'=>$district['name'],'bn_name'=>$district['bn_name'],'count'=>0);
   				$dis_val[$district['id']] = $temp;
   			}
   		}
   		$doctorRef = $database->collection('doctors');
   		$doctors = $doctorRef->documents();
   		foreach ($doctors as $doctor) {
   			if($doctor->exists()){
   				$dis_val[$doctor['districtId']]['count']++;
   			}
   		}

   		print_r($dis_val);
   		print_r("<pre>");
   		return $dis_val;
   	}

   	public function docoreGender()
   	{
   		$firestore = app('firebase.firestore');
   		$db = $firestore->database();
   		$doctorRef = $db->collection('doctors');
   		$doctors = $doctorRef->documents();
   		$doctor_gender = array('Male' => 0,'Female'=>0);
   		foreach ($doctors as $doctor) {
   			if($doctor->exists()){
	   			if($doctor['gender']=="Male"){

	   				$doctor_gender['Male']++;
	   			}else{
	   				$doctor_gender['Female']++;
	   			}

   			}
   		}
   			print_r("<pre>");
   			print_r($doctor_gender);
   			print_r("<pre>");

   		return $doctor_gender;
   	}

   	public function numberOfPatient()
   	{
   		$firestore = app('firebase.firestore');
   		$db = $firestore->database();
   		$patientRef = $db->collection('users');
   		$patients = $patientRef->documents();

   		$total_patient = 0;
   		// return count($patients);
   		print_r("<pre>");
   		foreach ($patients as $patient) {
   			// print_r($patient->data());
   			$total_patient++;
   		}
   		print_r("<pre>");

   		return $total_patient;

    }

    // new code 20-04-2020
    public function setdoctors(Request $request)
    {
        $firestore = app('firebase.firestore');
        $database = $firestore->database();

        $doctorRef = $database->collection('doctors');
        $docData = $doctorRef->documents();

        //$doctorRef->document('c9d0f0f84c29427388a5')->delete();

        $doctor = array();
        foreach($docData as $item){
            array_push($doctor,$item->data());
        }

        $flag = false;
        $emailFlag = false;
        
        // echo '<pre>';
        foreach($doctor as $key=>$item){
          if(isset($item['phone']) && $item['phone'] == $request->mobile){
            Session::flash('phonemsg','Contact number already exits.');
            $flag = true ;
            break;
          }
        }

        foreach($doctor as $key=>$item){
          if(isset($item['email']) && $item['email'] == $request->email){
                Session::flash('emailmsg','Email already exits.');
                $emailFlag = true ;
                break;
          }
        }
      
        $v = validator::make($request->all(),[
            'name' => 'required|alpha|max:15',
            'lastname' => 'required|alpha|max:10',
            'email' => 'required',
            'password' => 'required|min:8|alpha_num',
            'mobile' =>  'required|digits:11',
        ]);

        
        if($v->fails()){
            return redirect()->back()->withErrors($v->errors());
        }
        if($flag == true && $emailFlag == true){
          return redirect()->back();
        }elseif($flag == true && $emailFlag == false){
          return redirect()->back();
        }elseif($flag == false && $emailFlag == true){
          return redirect()->back();
        }

        $doctorRef = $database->collection('doctors')->newDocument();

        $password = $request->password ;

        $doctorRef->set([
            'uid' => $doctorRef->id(),
            'active'=> false,
            'email'=> $request->email,
            'name'=> $request->name.' '.$request->lastname,
            'phone' => $request->mobile,
            'password' => $password,
            //'role' => '',
            "totalRating" => 0,
            "price" => 0,
            "totalCount" => 0,
            "hospitalUid" => null,
            "hospitalized" => false,
            "online" => false
        ]);
        
        //$doctorRef->set($validateData);

        return redirect('login/doctor');
    }

    //29-04-2020
    public function setpatients(Request $request)
    {

        $firestore = app('firebase.firestore');
        $database = $firestore->database();
        $patientRef = $database->collection('users')->newDocument();
        $patientRef->set([
            'uid' => $patientRef->id(),
            'approve' => '',
            'online' => '',
            'active'=> true,
            'email'=> $request->email,
            'name'=> $request->name,
            'lastname' => $request->lastname,
            'phone' => $request->phone,
            'password' => $request->password,
            'gender' => '',
            'weight' => '',
            'height' => '',
            'bloodGroup' => '',
            'totalCount' => '',
            'totalRating' => '',
            'medication' => '',
            'smoke' => '',
            'photoUrl' => ''
        ]);

        return redirect('login/patient');
    }

    public function loggedIn(Request $request)
    {

        //dd($request->all());
        $firestore = app('firebase.firestore');
        $database = $firestore->database();

        $pass = $request->password ;

        //password encryption.....
        $method = "AES-256-CBC";
        $key = 'smarttechsolution';
        $password = openssl_encrypt($pass,$method,$key);
        echo '<br>Encrypted password : ';
        print_r($password);
        echo '<br>Decrypted password : ';
        $decrypt = openssl_decrypt($pass,$method,$key);
        print_r($decrypt);
        dd(1);
        //end

        if($request->title =='doctor'){
            $userRef = $database->collection('doctors');
        }
        else if($request->title =='patient'){
            $userRef = $database->collection('users');
        }
        else if ($request->title =='hospital'){
            $userRef = $database->collection('hospital_users');

            $v = validator::make($request->all(),[
                'password' => 'required',
                'common' =>  'required',
            ]);

            $email = $request->common ;
            $query = $userRef->where('email','=',$email)->where('password','=',$password);

            if($v->fails()){
                return redirect()->back()->withErrors($v->errors());
            }

            $userInfo = $query->documents();

            $userArr = array();

            foreach ($userInfo as $user) {
                if($user->exists()){
                    array_push($userArr, $user->data());
                }
            }

            if(!empty($userArr)){
                $request->session()->put('user',$userArr);

                //Edit from mafiz vai
                $MailSend = new MailSendController();
                $otp = mt_rand(10000,99999);
                $val = $MailSend->sendOtp($otp,$email);

                $helodoc2fa = array(
                    'title' => $request->title,
                    'opt' => $otp,
                    'status'=> false
                );

                $request->session()->put('helodoc2fa', $helodoc2fa);

                if ($val){
                    return redirect('/2fa')->with($helodoc2fa);
                }
            }


        }
        else if($request->title =='admin'){
            $userRef = $database->collection('admin');
            /*new code */
            //dd($userRef->documents());
            $v = validator::make($request->all(),[
                'password' => 'required',
                'common' =>  'required',
            ]);

            $email = $request->common ;
            $query = $userRef->where('email','=',$email)->where('password','=',$password);

            if($v->fails()){
                return redirect()->back()->withErrors($v->errors());
            }

            $userInfo = $query->documents();

            $userArr = array();

            foreach ($userInfo as $user) {
                if($user->exists()){
                    array_push($userArr, $user->data());
                }
            }

            if(!empty($userArr)){
                $request->session()->put('user',$userArr);

                //Edit from mafiz vai
                $MailSend = new MailSendController();
                $otp = mt_rand(10000,99999);
                $val = $MailSend->sendOtp($otp,$email);

                $helodoc2fa = array(
                    'title'	=> $request->title,
                    'opt'	=> $otp,
                    'status'=> false
                );

                $request->session()->put('helodoc2fa', $helodoc2fa);

                if ($val){
                    return redirect('/2fa')->with($helodoc2fa);
                }
            }

            /* end */


        }

       
        $v = validator::make($request->all(),[
            'password' => 'required|min:8|alpha_num',
            'common' =>  'required|max:14',
        ]);

        if($v->fails()){
            return redirect()->back()->withErrors($v->errors());
        }

        /*
        if(filter_var($request->common, FILTER_VALIDATE_EMAIL)){
            $email = $request->common ;
            $query = $userRef->where('email','=',$email)->where('password','=',$request->password);
        }else{
        */

        $phone = $request->common ;
        $query = $userRef->where('phone','=',$phone)->where('password','=',$password);
        // }

        $userInfo = $query->documents();

        $userArr = array();

        foreach ($userInfo as $user) {
            if($user->exists()){
                array_push($userArr, $user->data());
            }
        }

        if(!empty($userArr)){
            if(isset($userArr[0]['email']))$email = $userArr[0]['email'];
            /*$districtRef = $database->collection('districts');

            $data['district'] = $districtRef->documents();

            $data['districtList'] = array();

            foreach($data['district'] as $key=>$item){
                array_push($data['districtList'],$item->data());
            }*/

            $districtRef = $database->collection('districts');

            $data['district'] = $districtRef->where('active','=',true)->documents();
            $districtList = array();

            foreach($data['district'] as $key=>$item){
                array_push($districtList,$item->data());
            }

            $request->session()->put('user',$userArr);
            $request->session()->put('district',$districtList);

            //Edit from mafiz vai
            $MailSend = new MailSendController();
            $otp = mt_rand(10000,99999);
            $val = $MailSend->sendOtp($otp,$email);

            $helodoc2fa = array(
                'title'	=> $request->title,
                'opt'	=> $otp,
                'status'=> false
            );

            $request->session()->put('helodoc2fa', $helodoc2fa);

            if($request->title == 'hospital'){
                if($userArr[0]['login_attempt'] == false){
                    $hospital_user = $userRef->document($userArr[0]['hospitalUid']);
                    $hospital_user->update([
                        ['path' => 'login_attempt', 'value' => true]
                    ]);
                    $data['hospitalUser'] = $userArr[0]['hospitalUid'];
                    return view('frontend/new_pass')->with($data) ;
                }else{
                    if ($val){
                        return redirect('/2fa')->with($helodoc2fa);
                    }
                }

            }

            elseif ($val){
                return redirect('/2fa')->with($helodoc2fa);
            }

        }else{
            return redirect()->back()->withErrors($v->errors())->with('msg','Invalid email/mobile or password.');
        }
    }
    //End

    // 05-05-2020
    /*public function sethospitalusers(Request $request)
    {
        $firestore = app('firebase.firestore');
        $database = $firestore->database();
        $hosRef = $database->collection('hospital_users')->newDocument();
        $hosRef->set([
            'uid' => $hosRef->id(),
            'approve' => '',
            'online' => '',
            'active'=> '',
            'email'=> $request->email,
            'name'=> $request->name,
            'lastname' => $request->lastname,
            'phone' => $request->phone,
            'password' => $request->password,
            'plan' => ''
        ]);

        return redirect('login/hospital');
    }*/
    // End

    public function sethospitalusers(Request $request)
    {
        $firestore = app('firebase.firestore');
        $database = $firestore->database();

        $v = validator::make($request->all(),[
            'name'     => 'required|regex:/^[\pL\s\-]+$/u',
            'hospitalName' => 'required|max:25|regex:/^[\pL\s\-]+$/u',
            'hospitalAddress' => 'required|max:100',
            'phone' =>  'required|max:14',
            'plan' => 'required',
        ]);

        $hosRef = $database->collection('hospital_users');
        $hosData = $hosRef->documents();

        $hospital = array();
        foreach($hosData as $item){
            array_push($hospital,$item->data());
        }

        $flag = false;
        foreach($hospital as $key=>$item){
            if(isset($item['phone']) && $item['phone'] == $request->phone){
                $flag = true ;
                break;
            }
        }

        if($v->fails()){
            if($flag == true){
                Session::put('msg','phone number already exist.');
                return redirect()->back()->withErrors($v->errors())->with('msg','Contact number already exist.');
            }
            return redirect()->back()->withErrors($v->errors());
        }

        $hosRef = $database->collection('hospital_users')->newDocument();
        $hosRef->set([
            'hospitalUid' => $hosRef->id(),
            'online' => '',
            'active'=> false,
            'approve' => false,
            'email'=> $request->email,
            'name'=> $request->name,
            'phone' => $request->phone,
            'password' => '',
            'plan' => $request->plan,
            'hospitalName' => $request->hospitalName,
            'hospitalAddress' => $request->hospitalAddress,
            'comment' => $request->comment,
            'login_attempt' => false
        ]);

        Session::flash('notification','Please wait. After admin approve you, a email will sent to your mail.');
        //return redirect('login/hospital')->with('notification','A email will be sent to your email.');
        return redirect()->back();
    }

    public function manageDistrict(Request $r){
      $firestore = app('firebase.firestore');
      $database = $firestore->database();
      $disRef = $database->collection('districts');
        
      if(isset($r->submit)){
        //dd($r->all());
        $id = $r->disId;
        $district = $disRef->document($id);
        
        $district->update([
            ['path' => 'active' , 'value' => true]
        ]);
        Session::flash('msg','District activated.');
        return redirect('/admin/district');

      }else{
        $districtRef = $database->collection('districts');

            $data['district'] = $districtRef->documents();
            $data['districtList'] = array();

            foreach($data['district'] as $key=>$item){
                array_push($data['districtList'],$item->data());
            }
        return view('admin/district')->with($data);
      }

    }

    public function sendTempOtp(Request $request){
    
        $firestore = app('firebase.firestore');
        $database = $firestore->database();

        $reqData = $request->email ;

        $ref = $database->collection('doctors');
        $query = $ref->where('email','=',$reqData);
        $documents = $ref->documents();

        $data = array();
        foreach($documents as $item){
            array_push($data,$item->data());
        }

        $flag = false;
        foreach($data as $key=>$item){
            if(isset($item['email']) && $item['email'] == $reqData){
                $flag = true ;
                break;
            }
        }

        if($flag == false){
          Session::flash('notify','This email is not exists.');
          return redirect()->back();
        }else{
          $uid = '';
          foreach ($data as $key => $value) {
            if(isset($item['email']) && $item['email'] == $reqData){
                $uid = $item['uid'];
            }
          }
          
          $MailSend = new MailSendController();
          $temp_pass = 'telocure'.''.mt_rand(1000000,99999999);
          $val = $MailSend->sendResetPassword($temp_pass,$reqData);
          $userData = $ref->document($uid);
          $userData->update([
            ['path' => 'password' , 'value' => $temp_pass]
          ]);

          Session::flash('notify','Check your email.');
          return redirect()->back();
        }
    }

}
