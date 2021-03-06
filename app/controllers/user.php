<?php

/**
 * this class for add,edit and remove from user table
 * 
 * @author Faris Al-Otaibi
 */
class user extends CI_Controller{
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index(){
        if($this->core->checkPermissions('user','show','all')){
            $this->show();
        }else 
            redirect (STD_CMS_PERMISSION_PAGE);
    }
    
    public function show(){
        if($this->core->checkPermissions('user','show','all')){
            $segments = $this->uri->segment_array();
            $filter = isset($segments[3])? $segments[3]:'all';
            switch ($filter){
                
                case 'enable':
                    $this->db->where('isActive',1);
                    break;
                
                case 'disable':
                    $this->db->where('isActive',0);
                    break;
                
                case 'delete':
                    $this->db->where('isDelete',1);
                    break;
                
                case 'undelete':
                    $this->db->where('isDelete',0);
                    break;
                
                case 'all':
                default :
                    break;
            }
            $data['FILTER'] = $filter;
            $data['USERS'] = $this->users->getUsers();
            $data['CONTENT'] = "user";
            $data['STEP'] = 'show';
            $data['NAV'] = array(
                base_url()          => $this->lang->line('global_mainpage'),
                base_url().'admin'  => $this->lang->line('global_cpanel'),
                base_url().'user'   => $this->lang->line('global_users')
            );
            $data['TITLE'] = " -- ".$this->lang->line('global_users');
        }else
            redirect (STD_CMS_PERMISSION_PAGE);
        $this->core->load_template($data);
    }
    
    private function __check($type,$value)
    {
        if(empty($type) || empty($value))
            return false;
        
        if($type == 'username')
            $this->db->where('username',$value);
        elseif($type == 'email')
            $this->db->where('email',$value);
        else
            return false;
        $check = $this->users->getUsers();
        if(is_bool($check))
            return true;
        else
            return false;
    }
    
    public function add(){
        if($this->core->checkPermissions('user','add','all')){
            $this->load->model('groups');
            if($_POST){
                $password = $this->input->post('password',true);
                $repassword = $this->input->post('repassword',true);
                if($password == $repassword){
                    if($this->__check('username', $this->input->post('username',true)) && $this->__check('email', $this->input->post('email',true))){
                        $store = array(
                            'username'      => $this->input->post('username',true),
                            'full_name'     => $this->input->post('fullName',true),
                            'email'         => $this->input->post('email',true),
                            'password'      => $password,
                            'mobile'        => $this->input->post('mobile',true),
                            'isBanned'      => 0,
                            'isDelete'      => 0,
                            'isActive'      => 1,
                            'group_id'      => $this->input->post('group_id',true)
                        );
                        $this->users->addNewUser($store);
                        $this->load->library('email');
                        $site_name = $this->core->getSettingByName("site_name");
                        $site_email = $this->core->getSettingByName("site_email");

                        $this->email->from($site_email, '('.$site_name.'):');
                        $this->email->to($this->input->post('email',true));

                        $this->email->subject('('.$site_name.'): تسجيل جديد');
                        $message = '
                            <p>شكراً لأختيارك موقعنا نتمنى لك التوفيق</p>
                            <p>هذه الرسالة تأتي لتأكيد التسجيل لدينا </p>
                            <p>اسم المستخدم  :'.$this->input->post('username',true).'</p>
                            <p>'.anchor(base_url(),$site_name).'</p>
                        ';
                        $this->email->message($message);

                        $this->email->send();
                        $data['CONTENT'] = "msg";
                        $data['MSG'] = $this->lang->line('user_add_success_msg');

                    }else{
                        $data['ERROR'] = True;
                        $data['ERR_MSG'] = $this->lang->line('user_error_already_register_msg').anchor(base_url().'login/resetpassword',$this->lang->line('user_used_reset_password'));
                        $data['CONTENT'] = "user";
                        $data['GROUPS'] = $this->groups->getGroups('all');
                        $data['STEP'] = 'add';
                    }
                }else{
                    $data['ERROR'] = True;
                    $data['ERR_MSG'] = $this->lang->line('user_not_match_password');
                    $data['GROUPS'] = $this->groups->getGroups('all');
                    $data['CONTENT'] = "user";
                    $data['STEP'] = 'add';
                }
            }else{
                $data['ERROR'] = FALSE;
                $data['ERR_MSG'] = '';
                $data['GROUPS'] = $this->groups->getGroups('all');
                $data['CONTENT'] = "user";
                $data['STEP'] = 'add';
            }
        }else 
            redirect (STD_CMS_PERMISSION_PAGE);
        $data['NAV'] = array(
            base_url()          => $this->lang->line('global_mainpage'),
            base_url().'admin'  => $this->lang->line('global_cpanel'),
            base_url().'user'   => $this->lang->line('global_users')
        );
        $data['TITLE'] = " -- ".$this->lang->line('global_users');
        $this->core->load_template($data);
    }
    
    public function edit(){
        $this->load->model('groups');
        if($this->users->isLogin()){
            $segments = $this->uri->segment_array();
            if($this->core->checkPermissions('user','edit','all')){
                $userId = isset($segments[3])? $segments[3]:$this->users->getInfoUser('id');
                $data['ADMIN'] = true;
            }else{ 
                $userId = $this->users->getInfoUser('id');
                $data['ADMIN'] = false;
            }
            $userInfo = $this->users->getUser($userId);
            if(is_bool($userInfo))
                redirect(STD_CMS_ERROR_PAGE);
            
            if($_POST){
                $password = $this->input->post('password',true);
                $repassword = $this->input->post('repassword',true);
                if((!empty($password) && ($password == $repassword)) || empty($password)){
                    $store = array(
                            'username'      => $this->input->post('username',true),
                            'full_name'     => $this->input->post('fullName',true),
                            'email'         => $this->input->post('email',true),
                            'mobile'        => $this->input->post('mobile',true),
                            'group_id'      => $this->input->post('group_id',true)
                    );
                    if(!empty($password))
                        $store['password'] = $password;
                    
                    if($this->users->updateUser($userId,$store)){
                        $data['CONTENT'] = 'msg';
                        if($data['ADMIN'] == true)
                            $data['MSG'] = $this->lang->line('global_msg_success'). br(1).  anchor(base_url().'user', $this->lang->line('user_return_back'));
                        else
                            $data['MSG'] = $this->lang->line('global_msg_success'). br(1).  anchor(base_url().'', $this->lang->line('user_return_home'));
                    }else{
                        $data['username'] = $userInfo->username;
                        $data['fullname'] = $userInfo->full_name;
                        $data['email'] = $userInfo->email;
                        $data['mobile'] = $userInfo->mobile;
                        $data['group_id'] = $userInfo->group_id;
                        $data['ERROR'] = True;
                        $data['GROUPS'] = $this->groups->getGroups('all');
                        $data['ERR_MSG'] = $this->lang->line('user_error_do_not_update');
                        $data['CONTENT'] = "user";
                        $data['STEP'] = 'edit';
                    }
                }else{
                    $data['username'] = $userInfo->username;
                    $data['fullname'] = $userInfo->full_name;
                    $data['email'] = $userInfo->email;
                    $data['mobile'] = $userInfo->mobile;
                    $data['group_id'] = $userInfo->group_id;
                    $data['ERROR'] = True;
                    $data['GROUPS'] = $this->groups->getGroups('all');
                    $data['ERR_MSG'] = $this->lang->line('user_not_match_password');
                    $data['CONTENT'] = "user";
                    $data['STEP'] = 'edit';
                }
            }else{
                $data['username'] = $userInfo->username;
                $data['fullname'] = $userInfo->full_name;
                $data['email'] = $userInfo->email;
                $data['mobile'] = $userInfo->mobile;
                $data['group_id'] = $userInfo->group_id;
                $data['ERROR'] = False;
                $data['GROUPS'] = $this->groups->getGroups('all');
                $data['ERR_MSG'] = '';
                $data['CONTENT'] = "user";
                $data['STEP'] = 'edit';
            }
        }else 
            redirect (STD_CMS_PERMISSION_PAGE);
        $data['NAV'] = array(
            base_url()          => $this->lang->line('global_mainpage'),
        );
        if($this->users->isAdmin())
        {
            $data['NAV'][base_url().'admin'] = $this->lang->line('global_cpanel');
            $data['NAV'][base_url().'user'] = $this->lang->line('global_users');
        }else
             $data['NAV'][base_url().'myprofile'] = $this->lang->line('user_nav_myprofile');
        $data['TITLE'] = " -- ".$this->lang->line('global_users');
        $this->core->load_template($data);
    }


    public function action(){
        if($this->input->is_ajax_request()){
            $segments = $this->uri->segment_array();
            $type = isset($segments[3])? $segments[3]:NULL;
            $userId = isset($segments[4])? $segments[4]:0;
            $names = array(
                'enable'    => $this->lang->line('global_enable'),
                'disable'   => $this->lang->line('global_disable'),
                'delete'    => $this->lang->line('global_delete'),
                'restore'   => $this->lang->line('global_restore')
            );
            if(is_null($type) || $userId == 0)
                die($this->lang->line('user_action_url_error'));
            
            $user = $this->users->getUser($userId);
            if(is_bool($user))
                die($this->lang->line('user_action_user_not_found'));
            
            if($type == 'delete' || $type == 'restore')
                if($this->core->checkPermissions('user','delete','all')){
                    $store = array(
                        'isDelete' => ($type == 'delete')? 1:0
                    );
                }else
                    die($this->lang->line('user_action_permission_error'));
            else if($type == 'enable' || $type == 'disable')
                if($this->core->checkPermissions('user','active','all')){
                    $store = array(
                        'isActive' => ($type == 'enable')? 1 : 0
                    );
                }else
                    die($this->lang->line('user_action_permission_error'));
            else
                die($this->lang->line('user_action_url_error'));
            
            if($this->users->updateUser($userId,$store)){
                if($type == 'enable'){
                    $this->load->library('email');
                    $site_name = $this->core->getSettingByName("site_name");
                    $site_email = $this->core->getSettingByName("site_email");

                    $this->email->from($site_email, '('.$site_name.'):');
                    $this->email->to($user->email);

                    $this->email->subject('('.$site_name.'): تسجيل جديد');
                    $message = '
                        <p>شكراً لأختيارك موقعنا نتمنى لك التوفيق</p>
                        <p>تم تفعيل حسابك لدى موقعنا </p>
                        <p>اسم المستخدم  :'.$user->username.'</p>
                        <p>'.  anchor(base_url(), $site_name).'</p>
                    ';
                    $this->email->message($message);

                    $this->email->send();
                }
                
                die($this->lang->line('user_action_success_msg').$names[$type]);
            }else
                die($this->lang->line('user_action_not_work').$names[$type]);
            
        }else
            redirect(STD_CMS_ERROR_PAGE);
    }
}

?>
