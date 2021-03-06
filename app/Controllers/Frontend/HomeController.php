<?php 

namespace App\Controllers\Frontend;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;

use App\Helpers\ValidatorFactory;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Pagination\Paginator;

class HomeController
{
	public function getIndex()
	{ 
		$sliders  = Product::where('active_on_slider',true)->get();
		$products = Product::where('active',true)->paginate(8);

		view('index', ['sliders' => $sliders, 'products' => $products]);
	}

	public function getProduct($slug = NULL) 
	{
		if($slug == NULL) {
			redirect('/');
		}

		$product = Product::with('images')->where('slug',$slug)->first();

		if(empty($product)) {

			redirect('/');

		} else {

			view('product', ['product' => $product]);
		}
		
	}

	public function getProductlist()
	{
		if (isset($_GET['search']) && ($_GET['search'] != NULL)) {

			$search 	= $_GET['search'];
			$products 	= Product::where('active',true)->where('title','LIKE','%'.$search.'%')->get();

		} elseif (isset($_GET['category']) && ($_GET['category'] != NULL)) {

			$slug 		= $_GET['category'];
			$category	= Category::with('products')->where('slug',$slug)->get(); //die($category);
			$products 	= $category[0]->products; 

		} else {
			$products 	= Product::where('active',true)->get();
		}

		$categories = Category::withCount('products')->get();
		
		view('product-list', ['categories' => $categories, 'products' => $products]);
	}


	// LOGIN
	public function getLogin()
	{
		if ($_SESSION['login']) {
			redirect('/dashboard');
		}

		view('auth/login');
	}	

	public function postLogin()
	{
		$email 		= $_POST['email'];
		$password 	= $_POST['password'];

		$validator = (new ValidatorFactory())->make(
		    $data = [
		    	'email' 	=> $email,
		    	'password' 	=> $password,
		    ],
		    $rules = [
		    	'email' 	=> 'required|email',
		    	'password' 	=> 'required|min:6'
		    ]
		);

		if ($validator->fails()) {

			$_SESSION['login-email-error'] 		= $validator->errors()->get('email');
			$_SESSION['login-password-error'] 	= $validator->errors()->get('password');

			redirect('/login');
		}

		$user = User::where('email', $email)->first();

		if ($user) {

			$_SESSION['login']  				= false;
			$_SESSION['success']  				= NULL;
			$_SESSION['error']  				= NULL;
			$_SESSION['mailsend']  				= NULL;
			$_SESSION['login-email-error']		= NULL;
			$_SESSION['login-password-error']	= NULL;

			if (password_verify($password, $user->password)) {
			    
			    if (($user->email_verified_at == NULL) && ($user->email_verification_token != NULL)) {
			    	
					$_SESSION['error']  	= 'Email account not verified.';

					redirect('/login');

			    } else {

			    	$_SESSION['success']  	= 'Welcome '.$user->name.'.';
			    	$_SESSION['userid']  	= $user->id;
			    	$_SESSION['login']  	= true;

			    	redirect('/dashboard');
			    }

			} else {

				$_SESSION['error']  = 'Invalid password.';

				redirect('/login');
			}

		} else {

			$_SESSION['login-email-error']		= NULL;
			$_SESSION['login-password-error']	= NULL;
			$_SESSION['login']  				= false;
			$_SESSION['success']				= NULL;
			$_SESSION['error']  				= 'Invalid credentials.';

			redirect('/login');
		}

	}

	public function getRegister()
	{
		if ($_SESSION['login']) {
			redirect('/dashboard');
		}
		
		view('auth/register');
	}

	public function postRegister()
	{
		$name 		= $_POST['name'];
		$email 		= $_POST['email'];
		$password 	= $_POST['password'];
		$token 		= randomString();

		$validator = (new ValidatorFactory())->make(
		    $data = [
		    	'name' 		=> $name,
		    	'email' 	=> $email,
		    	'password' 	=> $password,
		    ],
		    $rules = [
		    	'name' 		=> 'required|min:3',
		    	'email' 	=> 'required|email',
		    	'password' 	=> 'required|min:6'
		    ]
		);

		if ($validator->fails()) {

			$_SESSION['validate-name'] 		= $validator->errors()->get('name');
			$_SESSION['validate-email'] 	= $validator->errors()->get('email');
			$_SESSION['validate-password'] 	= $validator->errors()->get('password');

			redirect('/register');
		}

		try {
			$user = User::create([
				'name' 						=> $name,
				'email' 					=> $email,
				'password' 					=> password_hash($password, PASSWORD_DEFAULT),
				'active' 					=> 0,
				'email_verification_token' 	=> $token
			]);

			$mail = new PHPMailer(true);                  
			try {
				//Server settings
				$mail->SMTPDebug 	= 2;                           
				$mail->isSMTP();                                
				$mail->Host 		= 'smtp.mailtrap.io';  			
				$mail->SMTPAuth 	= true;                         
				$mail->Username 	= '806bc3f34997d1';            
				$mail->Password 	= '7c70597697e1de';             
				$mail->SMTPSecure 	= 'tls';             			
				$mail->Port 		= 2525;                    			
	
				//Recipients
				$mail->setFrom('from@example.com', 'Mailer');
				$mail->addAddress($email, $name);  
	
				$httphost = $_SERVER['HTTP_HOST']; // Temporary
				//Content
				$mail->isHTML(true);                          
				$mail->Subject = "Verify you email account";
				$mail->Body    = "Dear $name, <br><br>Please active your account by click link: <br><a href='http://$httphost/active/$token'>Active Your Account.</a>";
				$mail->AltBody = "Please active you account by using the link: http://$httphost/active/$token";

				$mail->send();
	
			} catch (Exception $e) {
				// echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;

				$_SESSION['success'] 			= NULL;
				$_SESSION['validate-name'] 		= NULL;
				$_SESSION['validate-email'] 	= NULL;
				$_SESSION['validate-password'] 	= NULL;
				$_SESSION['error'] 				= 'Account activation message could not be sent. '.$mail->ErrorInfo;
	
				redirect('/register');
			}

			$_SESSION['mailsend'] 			= 'Activation mail has been sent.';
			$_SESSION['success']  			= 'Registration completed successfully.';
			$_SESSION['error'] 				= NULL;
			$_SESSION['validate-name'] 		= NULL;
			$_SESSION['validate-email'] 	= NULL;
			$_SESSION['validate-password'] 	= NULL;

			redirect('/login');

		} catch (\Exception $e) {

			$_SESSION['success'] 			= NULL;
			$_SESSION['validate-name'] 		= NULL;
			$_SESSION['validate-email'] 	= NULL;
			$_SESSION['validate-password'] 	= NULL;
			$_SESSION['error'] 				= 'Email address is already taken!';

			redirect('/register');
		}
	}

	public function getActive($active = '')
	{
		$user = User::where('email_verification_token', $active)->first();

		if ($user) {
			
			$user->update([
				'email_verified_at' 		=> Carbon::now(),
				'email_verification_token' 	=> NULL,
				'active' 					=> 1
			]);

			$_SESSION['mailsend'] 	= NULL;
			$_SESSION['error'] 		= NULL;
			$_SESSION['success']  	= 'Account activated successfully.';

			redirect('/login');

		} else {

			$_SESSION['mailsend'] 	= NULL;
			$_SESSION['success']  	= NULL;
			$_SESSION['error']  	= 'Incorrect activation token.';

			redirect('/login');
		}
	}

	public function getLogout()
	{
		unset($_SESSION['login']);
		unset($_SESSION['userid']);

		$_SESSION['success'] 	= 'You have been logout.';
		$_SESSION['error'] 		= NULL;

		redirect('/login');
	}



}