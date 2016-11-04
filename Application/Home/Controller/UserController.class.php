<?php 
namespace Home\Controller;
use Think\Controller;
class UserController extends Controller{
	public function index(){
		echo 'this is Home UserController';
	}
	public function register(){
		//判断是加载页面还是提交表单
		if(IS_POST){
			$username = I('post.username');
			$password = I('post.password');
			$mail = I('post.mail');
			$salt = md5(time());
			$data =array(
				'username' => $username,
				'password' => $password,
				'email' => $mail,
				'salt' => $salt
				);
			$rs = M('user')->add($data);
			if($rs){
				$sendRs = sendMail("用户激活邮件","尊敬的$username,感谢你的注册,<a href='http://web.api.com/Home/User/active/id/$rs/salt/$salt'>请点击激活用户</a>",$mail);
				if($sendRs === true){
					$this->success('注册成功',U('User/index'),3);
				}				
			}else{
				$this->error('注册失败',U('User/register'),3);
			}
		}else{
			$this->display();
		}
		
	}
	public function active(){
		$id = I('get.id');
		$salt = I('get.salt');
		$saltRs = M('user')->where("salt = '$salt'")->find();
		if($saltRs === NULL){
			$this->error('非法激活',U('User/index'),3);
		}else{
			$rs = M('user')->where("id = $id and salt = '$salt'")->setField('active',1);
			if($rs){
				$this->success('用户激活成功',U('User/index'),3);
			}else{
				$this->error('激活失败',U('User/index'),3);
			}
		}
	}
}
