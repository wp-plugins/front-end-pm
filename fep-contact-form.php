<?php

/******************************************FEP CONTACT FORM BEGIN******************************************/
//FEP Contact Form class
if (!class_exists("fep_cf_class"))
{
  class fep_cf_class
  {
 
 function __construct()
    {
      $this->adminOps = $this->getAdminOps();
    }
	
	var $adminOpsName = "FEP_cf_options";
    var $adminOps = array();
  
	
	    function CFmenuPage()
    {
      if ($this->pmAdminSave())
        echo "<div id='message' class='updated fade'><p>".__("Options successfully saved", "fep")."</p></div>";
      $viewAdminOps = $this->getAdminOps(); //Get current options
	  $url = 'http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/';
	  $fepURL = admin_url( 'admin.php?page=fep-admin-settings' );
	  $ReviewURL = 'https://wordpress.org/support/view/plugin-reviews/front-end-pm';
	  $fep = new fep_main_class();
	  $token = $fep->fep_create_nonce();
	  require_once('js/cfjs.js');
	  $records = get_option('fep_cf_to_field');
      echo 	"<div class='wrap'>
          <h2>".__("FEP Contact Form Settings", "fep")."</h2>
		  <h4>".sprintf(__("For FRONT END PM Settings <a href='%s' >Click Here</a>", "fep"),esc_url($fepURL))."</h4>
		  <h5>".sprintf(__("If you like this plugin please <a href='%s' target='_blank'>Review in Wordpress.org</a> and give 5 star", "fep"),esc_url($ReviewURL))."</h5>
          <form id='fep-admin-save-form' name='fep-admin-save-form' method='post' action=''>
	<table id='options-table' class='widefat'>
	  <thead><tr><th>Department Name</th><th>Username</th><th>&nbsp;</th></tr></thead><tr><td><input type='button' class='fep_cf_add' value='Add More' /></td><td>(Username of person who will receive messages of that Department)</td></tr>";
	  if(count($records)){
		 foreach($records as $key => $eachRecord){
		echo "	<tr>
				<td><input type='text'  pattern='.{3,}' required name='dp_name[]' value='".stripslashes($key)."'/></td>
				<td><input type='text' 	pattern='.{3,}' required name='dp_username[]' value='".$eachRecord."' /></td>                        
				<td><input type='button' class='fep_cf_del' value='Delete' /></td>
			</tr>";} } else { 
			echo "
			<tr>
				<td><input type='text'  pattern='.{3,}' required name='dp_name[]' value=''/></td>
				<td><input type='text'  pattern='.{3,}' required name='dp_username[]' value='' /></td>
				<td><input type='button' class='fep_cf_del' value='Delete' /></td>
			</tr>";
			} 
			echo "
			
		</table>";
		  echo "<span><input class='button-primary' type='submit' name='fep-cf-admin-save' value='".__("Save Options", "fep")."' /></span>";
          echo "<table class='widefat'>
          <thead>
          <tr><th width='30%'>".__("Setting", "fep")."</th><th width='70%'>".__("Value", "fep")."</th></tr>
          </thead>
		  <tr><td>".__("Required Fields", "fep")."<br/><small>".__("Name, Email, Subject, Message always required.", "fep")."</small></td><td><input type='text' size='10' name='fep_cf_req' value='".$viewAdminOps['fep_cf_req']."' /><br/><small>".__("Separated by comma. Available (Address,Website)", "fep")."</small></td></tr>
		  <tr><td>".__("Bad words", "fep")."<br /><small>".__("Separated by comma", "fep")."</small></td><td><TEXTAREA name='fep_cf_bad'>".$viewAdminOps['fep_cf_bad']." </TEXTAREA></td></tr>
		  <tr><td>".__("Email Footer", "fep")."<br /><small>".__("For sending email", "fep")."</small></td><td><TEXTAREA name='fep_cf_efoot'>".$viewAdminOps['fep_cf_efoot']." </TEXTAREA></td></tr>
		  <tr><td>".__("Block IP", "fep")."<br /><small>".__("Separated by comma", "fep")."</small></td><td><TEXTAREA name='fep_ip_block'>".$viewAdminOps['fep_ip_block']." </TEXTAREA></td></tr>
		  <tr><td>".__("Maximum points before mark as spam", "fep")."<br /></td><td><input type='text' size='30' name='fep_cf_point' value='".$viewAdminOps['fep_cf_point']."' /><br /><small>".__("Default: 4", "fep")."</small></td></tr>
		  <tr><td>".__("Time delay between two messages send by a user via FEP Contact Form in minutes (0 = No delay required)", "fep")."<br /></td><td><input type='text' size='30' name='cf_time_delay' value='".$viewAdminOps['cf_time_delay']."' /><br /><small>".__("Applicable only for registered users", "fep")."</small></td></tr>
		  <tr><td colspan='2'><input type='checkbox' name='fep_cf_cap' ".checked($viewAdminOps['fep_cf_cap'], 'on', false)." /> ".__("Enable CAPTCHA?", "fep")."<br /><small>".__("Configure CAPTCHA below", "fep")."</small></td></tr>
		  <tr><td>".__("CAPTCHA Question", "fep")."<br /><small>".__("It will show on FEP Contact Form", "fep")."</small></td><td><input type='text' size='30' name='fep_cf_capqs' value='".$viewAdminOps['fep_cf_capqs']."' /></td></tr>
		  <tr><td>".__("CAPTCHA Answer", "fep")."<br /><small>".__("Have to be same answer to send contact message.", "fep")."</small></td><td><input type='text' size='30' name='fep_cf_capans' value='".$viewAdminOps['fep_cf_capans']."' /></td></tr>
		  <tr><td colspan='2'><input type='checkbox' name='fep_cf_logged' ".checked($viewAdminOps['fep_cf_logged'], 'on', false)." /> ".__("Require logged in to send contact message?", "fep")."</td></tr>
		  <tr><td colspan='2'><input type='checkbox' name='fep_cf_akismet' ".checked($viewAdminOps['fep_cf_akismet'], 'on', false)." /> ".__("Enable AKISMET check?", "fep")."<br /><small>".__("Need AKISMET plugin installed.", "fep")."</small></td></tr>
		  
          <tr><td colspan='2'><span><input class='button-primary' type='submit' name='fep-admin-save' value='".__("Save Options", "fep")."' /></span></td><td><input type='hidden' name='token' value='$token' /></td></tr>
          </table>
		  </form>
		  <ul>".sprintf(__("For more info or report bug pleasse visit <a href='%s' target='_blank'>Front End PM</a>", "fep"),esc_url($url))."</ul>
          </div>";
    }
	
      function pmAdminSave()
    {
      if (isset($_POST['fep-admin-save']) || isset($_POST['fep-cf-admin-save']))
      {
	  $fep = new fep_main_class();
	  $postedToken = filter_input(INPUT_POST, 'token');
	  
	  if ( !$fep->fep_verify_nonce($postedToken)) {
  		wp_die("<div id='message' class='error'><p>".__("Sorry, your nonce did not verify", "fep")."</p></div>"); }
		
	  if (isset($_POST['fep-admin-save'])){
	  if (!ctype_digit($_POST['fep_cf_point']) || !ctype_digit($_POST['cf_time_delay'])) {
	  echo "<div id='message' class='error'><p>".__("Please set points and time delay!", "fep")."</p></div>";
	  return;}
	 
        $saveAdminOps = array('fep_cf_req' 	=> $_POST['fep_cf_req'],
                              'fep_cf_bad' => $_POST['fep_cf_bad'],
							  'fep_cf_efoot' => $_POST['fep_cf_efoot'],
							  'fep_ip_block' => $_POST['fep_ip_block'],
							  'fep_cf_point' => $_POST['fep_cf_point'],
							  'cf_time_delay' => $_POST['cf_time_delay'],
							  'fep_cf_cap' => $_POST['fep_cf_cap'],
							  'fep_cf_capqs' => $_POST['fep_cf_capqs'],
                              'fep_cf_capans' => $_POST['fep_cf_capans'],
							  'fep_cf_logged' => $_POST['fep_cf_logged'],
							  'fep_cf_akismet' => $_POST['fep_cf_akismet']
        );
        update_option($this->adminOpsName, $saveAdminOps);
      } 
	  if (isset($_POST['fep-admin-save']) || isset($_POST['fep-cf-admin-save'])){
	  
	  $dp_name = str_replace(",", " ",$_POST['dp_name']); //make sure we don't get ,(comma) in department name
	  $dp_name = implode(",",$dp_name);
	  $dp_name = $fep->input_filter($dp_name);
	  $dp_name = explode (",", $dp_name);
	  $dp_username = str_replace(array(',',' '),array('(comma)','(white-space)'),$_POST['dp_username']); //make sure we don't get (comma) and (white-space) in department username
	  $dp_username = implode(",",$dp_username);
	  $dp_username = explode (",",$dp_username);
	  foreach($dp_username as $wpuser){
		$wpuser = trim($wpuser);
		if($wpuser!=''){
			if(!username_exists($wpuser)){
			echo "<div id='message' class='error'><p>".__("Username $wpuser is invalid!", "fep")."</p></div>";
	  return;}
			} }
	  $record = array_combine($dp_name , $dp_username);
	  
	  update_option('fep_cf_to_field', $record);
	  }
	  return true;
	  }
      return false;
    }

    function getAdminOps()
    {
      $pmAdminOps = array('fep_cf_req' => '',
                          'fep_cf_bad' => 'ahole,anus,ash0le,ash0les,asholes,ass,Ass Monkey,Assface,assh0le,assh0lez,asshole,assholes,assholz,asswipe,azzhole,bassterds,bastard,bastards,bastardz,basterds,basterdz,Biatch,bitch,bitches,Blow Job,boffing,butthole,buttwipe,c0ck,c0cks,c0k,Carpet Muncher,cawk,cawks,Clit,cnts,cntz,cock,cockhead,cock-head,cocks,CockSucker,cock-sucker,crap,cum,cunt,cunts,cuntz,dick,dild0,dild0s,dildo,dildos,dilld0,dilld0s,dominatricks,dominatrics,dominatrix,dyke,enema,f u c k,f u c k e r,fag,fag1t,faget,fagg1t,faggit,faggot,fagit,fags,fagz,faig,faigs,fart,flipping the bird,fuck,fucker,fuckin,fucking,fucks,Fudge Packer,fuk,Fukah,Fuken,fuker,Fukin,Fukk,Fukkah,Fukken,Fukker,Fukkin,g00k,gay,gayboy,gaygirl,gays,gayz,God-damned,h00r,h0ar,h0re,hells,hoar,hoor,hoore,jackoff,jap,japs,jerk-off,jisim,jiss,jizm,jizz,knob,knobs,knobz,kunt,kunts,kuntz,Lesbian,Lezzian,Lipshits,Lipshitz,masochist,masokist,massterbait,masstrbait,masstrbate,masterbaiter,masterbate,masterbates,Motha Fucker,Motha Fuker,Motha Fukkah,Motha Fukker,Mother Fucker,Mother Fukah,Mother Fuker,Mother Fukkah,Mother Fukker,mother-fucker,Mutha Fucker,Fuker,Fukker,orgasim;,orgasm,orgasum,oriface,orifice,orifiss,packi,packie,packy,paki,pakie,paky,pecker,peeenus,peeenusss,peenus,peinus,pen1s,penas,penis,penis-breath,penus,penuus,Phuc,Phuck,Phuk,Phuker,Phukker,pusse,pussee,pussy,puuke,puuker,queer,queers,queerz,qweers,qweerz,qweir,recktum,rectum,retard,scank,schlong,screwing,semen,sex,sexy,Sh!t,sh1t,sh1ter,sh1ts,sh1tter,sh1tz,shit,shits,shitter,Shitty,Shity,shitz,Shyt,Shyte,Shytty,Shyty,skanck,skank,skankee,skankey,skanks,Skanky,slut,sluts,Slutty,slutz,son-of-a-bitch,tit,turd,va1jina,vag1na,vagiina,vagina,vaj1na,vajina,vullva,vulva,w0p,wh00r,wh0re,whore,xrated,xxx,b!+ch,bitch,blowjob,clit,arschloch,shit,ass,asshole,b!tch,b17ch,b1tch,bastard,bi+ch,boiolas,buceta,c0ck,cawk,chink,cipa,clits,cock,cum,cunt,dildo,dirsa,ejakulate,fatass,fcuk,fuk,fux0r,hoer,hore,jism,kawk,l3itch,l3i+ch,lesbian,masturbate,masterbat,masterbat3,motherfucker,s.o.b.,mofo,nazi,nigga,nigger,nutsack,phuck,pimpis,pusse,pussy,scrotum,shemale,shi+,sh!+,slut,smut,teets,tits,boobs,b00bs,teez,testical,testicle,titt,w00se,jackoff,wank,whoar,whore,damn,dyke,shit,@$$,amcik,andskota,arse,assrammer,ayir,bi7ch,bitch,bollock,breasts,butt-pirate,Cock,cunt,d4mn,daygo,dego,dick,dike,fag,fanculo,fanny,feces,feg,Felcher,ficken,fitt,Flikker,foreskin,Fotze,Fu(,fuk,futkretzn,gay,gook,guiena,h0r,h4x0r,hell,helvete,hoer,honkey,Huevon,hui,injun,jizz,lesbo,mamhoon,piss,pizda,poontsee,poop,porn,p0rn,pr0n,preteen,pula,pule,puta,puto,screw',
						  'fep_cf_efoot' => 'Please DO NOT reply to this email directly because we do not check inbox of this email.',
						  'fep_ip_block' => '',
						  'fep_cf_point' => 4,
						  'cf_time_delay' => 10,
						  'fep_cf_cap' => false,
						  'fep_cf_capqs' => '',
						  'fep_cf_capans' => '',
						  'fep_cf_logged' => false,
						  'fep_cf_akismet' => false
      );

      //Get old values if they exist
      $adminOps = get_option($this->adminOpsName);
      if (!empty($adminOps))
      {
        foreach ($adminOps as $key => $option)
          $pmAdminOps[$key] = $option;
      }

      update_option($this->adminOpsName, $pmAdminOps);
      $this->adminOps = $pmAdminOps;
      return $pmAdminOps;
    }
	
/******************************************CONTACT FORM BEGIN******************************************/
	
  function contact_form(){
	$html = '<h2>' . __('Send Message', 'fep') . '</h2>';
	if(isset($_POST['contact_message'])!=''){
		$errors = $this->checkContact();
		if(count($errors->get_error_messages())>0){
			$html .= $this->Error($errors);
			$html .= $this->fep_contact_form();
		}
		else{
			$html .= '<div id="fep-success">' .__("Message successfully send.", "fep"). ' </div>';
		}
	}
	else{
		$html .= $this->fep_contact_form();
	}
	return $html;
}
	
	    function fep_contact_form()
    {
      global $user_ID, $user_login;
	  $fep = new fep_main_class();
	  $token = $fep->fep_create_nonce();
      $adminOps = $this->getAdminOps();
	  //get department names and usernames of those departments users
	  $records = get_option('fep_cf_to_field');
		if (!$fep->have_permission())
		{
		return '<div id="fep-error">' .__("You cannot send messages because you are blocked by administrator!", "fep"). ' </div>';
      }
      if (is_user_logged_in() || $adminOps['fep_cf_logged'] != 'on')
      {
	  $message_from = ( isset( $_REQUEST['message_from'] ) ) ? $_REQUEST['message_from']: '';
	  $message_email = ( isset( $_REQUEST['message_email'] ) ) ? $_REQUEST['message_email']: '';
	  $website = ( isset( $_REQUEST['website'] ) ) ? $_REQUEST['website']: '';
	  $address = ( isset( $_REQUEST['address'] ) ) ? $_REQUEST['address']: '';
	$message_title = ( isset( $_REQUEST['message_title'] ) ) ? $_REQUEST['message_title']: '';
	$message_content = ( isset( $_REQUEST['message_content'] ) ) ? $_REQUEST['message_content']: '';
	
		$newMsg = "<form name='contact_message' action='' method='post'>";
        $newMsg .= __("Department", "fep")."<font color='red'>*</font>: <br />";
		if(count($records)){
		 foreach($records as $key=>$eachRecord){
		$newMsg .="<label><input type='radio' name='message_to' value='$eachRecord' /> ".stripslashes($key)."</label>
		<input type='hidden' name='department' value='".stripslashes($key)."' /><br />";}
		} else {
		$newMsg .=__("Please add departments from FEP contact form settings in backend.","fep")."<br />";}
		
		$newMsg .= __("Name", "fep")."<font color='red'>*</font>: <br />";
		if (is_user_logged_in()) {
		$newMsg .= $fep->convertToDisplay($user_login). "<br />"; 
		} else {
		$newMsg .="<input type='text' name='message_from' placeholder='Type your Name' maxlength='65' value='$message_from' /><br/>";
		$newMsg .= __("Email", "fep")."<font color='red'>*</font>: <br />";
		$newMsg .="<input type='text' name='message_email' placeholder='Type your Email Address' maxlength='65' value='$message_email' /><br/>";}
		$newMsg .= __("Website", "fep").": <br />";
		$newMsg .="<input type='text' name='website' value='$website' /><br/>";
		$newMsg .= "<div id='fep-hd'>".__("H Name", "fep").":";
		$newMsg .="<input type='text' name='name1' value='' /><br />";
		$newMsg .= __("H Email", "fep").":";
		$newMsg .="<input type='email' name='email1' value='' /></div>";
		$newMsg .="<noscript><input type='hidden' name='nojs' value='nojs' /></noscript>";
		$newMsg .= __("Address", "fep").": <br />";
		$newMsg .="<input type='text' name='address' value='$address' /><br/>";
        $newMsg .= __("Subject", "fep")."<font color='red'>*</font>:<br/>
        <input type='text' name='message_title' placeholder='Subject' maxlength='65' value='$message_title' /><br/>".
        __("Message", "fep")."<font color='red'>*</font>:<br/>".$fep->get_form_buttons()."<br/>
        <textarea name='message_content' placeholder='Message Content'>$message_content</textarea><br/>";
		if ($adminOps['fep_cf_cap'] == 'on')
      {
		$newMsg .= __("CAPTCHA question", "fep")."<font color='red'>*</font>:<br/>";
		$newMsg .= $adminOps['fep_cf_capqs']."<br />";
		$newMsg .= __("CAPTCHA answer", "fep")."<font color='red'>*</font>:<br/>";
		$newMsg .= "<input type='text' name='cap_ans' autocomplete='off' value='' /><br/>";}
		$newMsg .= "<input type='hidden' name='token' value='$token' /><br/>
        <input type='submit' name='contact_message' value='".__("Send Message", "fep")."' />
        </form>";
        
        return $newMsg;
      }
      else
      {
        return '<div id="fep-error">' .__("Please log in to contact with us.", "fep"). ' </div>';
      }
    }
	
	function checkContact()
	{
	if (isset($_POST['contact_message'])){
		global $wpdb, $user_ID, $user_login, $current_user;
		get_currentuserinfo();
		$fep = new fep_main_class();
		$errors = new WP_Error();
		
		$adminOps = $this->getAdminOps();
		
		$preTo = trim($_POST['message_to']);
		$to = $fep->convertToID($preTo);
		$department = trim($_POST['department']);
		$fromAddress = $fep->input_filter($_POST['address']);
		$title = $fep->input_filter($_POST['message_title']);
      	$content = $fep->input_filter($_POST['message_content']);
		$website = esc_url(trim($_POST['website']));
		$send_date = current_time('mysql');
		$ip = $this->get_ip();
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$referer = $_SERVER['HTTP_REFERER'];
		$read = 5;
		
		if (is_user_logged_in()) {
		//$fromID = $user_ID;
		$fromID = $current_user->ID;
		$fromName = $current_user->display_name;
		$fromEmail = $current_user->user_email;
		} else {
		$fromID = 0;
		$fromName = sanitize_text_field($_POST['message_from']);
		$fromEmail = trim($_POST['message_email']);
		}
		
        if (!$to)
		  $errors->add('invalidTo', __('You must select a department.', 'fep'));
		if (!$fromName)
		  $errors->add('invalidName', __('You must enter your name.', 'fep'));
		if (!is_email($fromEmail))
		  $errors->add('invalidEmail', __('You must enter your valid e-mail address.', 'fep'));
        if (!$title)
		  $errors->add('invalidSub', __('You must enter subject.', 'fep'));
        if (!$content)
		  $errors->add('invalidMgs', __('You must enter some messages.', 'fep'));
		  
		$requiredFields = explode(',', $adminOps['fep_cf_req']);
		$requiredFields = array_unique($requiredFields);
		foreach($requiredFields as $field) {
		$field = strtolower(trim($field));
		if ( $field !='' ) { //if field have value
		if (!isset($_POST[$field]) || empty($_POST[$field]))
			$errors->add('invalidReq', sprintf(__('%s is required.', 'fep'), ucwords($field)));
	} }
	if ($adminOps['fep_cf_cap'] == 'on')
      {
		if (!isset($_POST['cap_ans']) || $_POST['cap_ans'] != $adminOps['fep_cf_capans'] )
		  $errors->add('capCheck', __('CAPTCHA answer is incorrect.', 'fep'));}
		  
		 if (!isset($_POST['name1']) || !empty($_POST['name1']) || !isset($_POST['email1']) || !empty($_POST['email1']))
		  $errors->add('BotCheck', __('If you see "H Name" or "H Email" Field DO NOT fill those.Those for Bot check.', 'fep'));
		 
		 if (is_user_logged_in()) {
		 $timeDelay = $fep->TimeDelay($adminOps['cf_time_delay']);
	  if ($timeDelay['diffr'] < $adminOps['cf_time_delay'] && !current_user_can('manage_options'))
      {
	  $errors->add('TimeDelay', sprintf(__('Please wait at least more %s to send another message!', 'fep'), $timeDelay['time']));
      }}
		  
		if ($this->isBot() !== false)
		  $errors->add('Bots', sprintf(__("No bots please! UA reported as: %s", "fep"), $_SERVER['HTTP_USER_AGENT'] ));
		  
		  $ipBlock = explode(',', $adminOps['fep_ip_block']);
		  $ipBlock = array_unique($ipBlock);
		  if(in_array($ip, $ipBlock))
			$errors->add('ipBlock', sprintf(__("Your IP %s is Blocked.", "fep"), $ip ));
		  
	// lets check a few things - not enough to trigger an error on their own, but worth assigning a spam score..
	// score quickly adds up therefore allowing genuine users with 'accidental' score through but cutting out real spam :)
	$points = (int)0;

	$badwords = explode(',', $adminOps['fep_cf_bad']);
	$badwords = array_unique($badwords);

	foreach ($badwords as $word) {
		if ( strpos(strtolower($fromAddress), trim(strtolower($word))) !== false || strpos(strtolower($title), trim(strtolower($word))) !== false || strpos(strtolower($content), trim(strtolower($word))) !== false || strpos(strtolower($fromName), trim(strtolower($word))) !== false )
			$points += 2; }

	if (strpos(strtolower($content), "http://") !== false || strpos(strtolower($content), "www.") !== false)
		$points += 2;
	if (isset($_POST['nojs']))
		$points += 1;
	if (strlen($fromName) < 3 || strlen($fromName) > 20)
		$points += 1;
	if (strlen($title) < 10 || strlen($title > 100))
		$points += 2;
	if (strlen($content) < 15 || strlen($content > 1500))
		$points += 2;
	// end score assignments
	if ( $points > $adminOps['fep_cf_point'] )
	$errors->add('spamPoints', __("Your message looks too much like spam, and could not be sent this time. [$points]", 'fep'));
	
	if ( $adminOps['fep_cf_akismet'] == 'on' ) {
	// Check if Akismet is installed with the corresponding API key
if( function_exists( 'akismet_http_post' ))
{   
	$akwp_api_key = get_option('wordpress_api_key');
	//Check Akismet API key
	if (!empty($akwp_api_key)) {
    global $akismet_api_host, $akismet_api_port;

    // data package to be delivered to Akismet
    $data = array( 
        'comment_author'        => $fromName,
        'comment_author_email'  => $fromEmail,
        'comment_author_url'    => $website,
        'comment_content'       => $content,
        'user_ip'               => $ip,
        'user_agent'            => $browser,
        'referrer'              => $referer,
        'blog'                  => get_bloginfo('wpurl'),
        'blog_lang'             => get_bloginfo('language'),
        'blog_charset'          => get_bloginfo('charset'),
        'permalink'             => $referer
    );

    // construct the query string
    $query_string = http_build_query( $data );
    // post it to Akismet
    $response = akismet_http_post( $query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port );
    // check the results        
    $result = ( is_array( $response ) && isset( $response[1] ) ) ? $response[1] : 'false';
	
	if ($result == true )
	$read = 7;
	} else {
	if (current_user_can('manage_options'))
	 print '<div id="fep-error">' .__("AKISMET KEY is not configured.", "fep"). ' </div>';
	}
} else {
if (current_user_can('manage_options'))
 print '<div id="fep-error">' .__("AKISMET plugin is not installed.", "fep"). ' </div>';
 }
 }
	

		 // Check if a form has been sent
		$postedToken = filter_input(INPUT_POST, 'token');
	  if (empty($postedToken))
      {
        $errors->add('emptyToken', __('Empty Token.', 'fep'));
      }

		if((count($errors->get_error_codes())==0) &&  $fep->fep_verify_nonce($postedToken)){
		 
		 $wpdb->query($wpdb->prepare("INSERT INTO {$fep->fepTable} (from_user, from_name, from_email, to_user, department, last_sender, send_date, last_date, message_title, message_contents, message_read) VALUES ( %d, %s, %s, %d, %s, %d, %s, %s, %s, %s, %d)", $fromID, $fromName, $fromEmail, $to, $department, $fromID, $send_date, $send_date, $title, $content, $read));
		$message_id = $wpdb->insert_id;
		if ($message_id) {
		$wpdb->query($wpdb->prepare('INSERT INTO '.$fep->cfTable.' (message_id, field_name, field_value) VALUES ( %d, "ip", %s ),( %1$d, "address", %s ),( %1$d, "website", %s ),( %1$d, "browser", %s ),( %1$d, "referer", %s ),( %1$d, "Spam Points", %s )', $message_id, $ip, $fromAddress, $website, $browser, $referer, $points));
		if ( $read == 5 )
		$this->sendDepartmentEmail($to, $fromName, $title, $content, $referer);
		} else {
		$errors->add('someWrong', __('Something wrong please try again!', 'fep'));}
	}
      
	return $errors;
      }
	
	}
	
	function sendDepartmentEmail($to, $fromName, $title, $content, $referer)
    {
	$fep = new fep_main_class();
	
      $toOptions = $fep->getUserOps($to);
      $notify = $toOptions['allow_emails'];
      if ($notify == 'true')
      {
        $sendername = get_bloginfo("name");
        $sendermail = get_bloginfo("admin_email");
        $headers = "MIME-Version: 1.0\r\n" .
          "From: ".$sendername." "."<".$sendermail.">\r\n" . 
          "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\r\n";
		$subject = "" . get_bloginfo("name").": New Contact Message";
		$message = "You have received a new contact message in \r\n";
		$message .= get_bloginfo("name")."\r\n";
		$message .= "From: ".$fromName. "\r\n";
		$message .= "Subject: ".$title. "\r\n";
		// message lines should not exceed 70 characters (PHP rule), so wrap content
		$message .= "Message: ".wordwrap($content, 70). "\r\n";
		$message .= "Referrer: ".$referer. "\r\n";
		$message .= "Please Click the following link to view full Message. \r\n";
		$message .= get_permalink($fep->getPageID)."&fepaction=mycontactmgs\r\n";	
        $mUser = get_userdata($to);
        $mailTo = $mUser->user_email;
		
		//wp_mail($mailTo, $subject, $message, $headers); // uncomment this line if you want blog name in message from, comment following line
        wp_mail($mailTo, $subject, $message);
      }
    }

/******************************************CONTACT FORM END******************************************/

/******************************************SEND EMAIL BEGIN******************************************/

// Send email to any email address
	function NewEmail(){
	$html = '<h2>' . __('Send Email', 'fep') . '</h2>';
	if(isset($_POST['fep-send-email'])!=''){
		$errors = $this->send_email_action();
		if(count($errors->get_error_messages())>0){
			$html .= $this->Error($errors);
			$html .= $this->send_email();
		}
		else{
			$html .= '<div id="fep-success">' .__("Email successfully send.", "fep"). ' </div>';
		}
	}
	else{
		$html .= $this->send_email();
	}
	return $html;
}

	function send_email() 
	{
	global $user_login;

$tocheck = get_option('fep_cf_to_field');
//permission check
if (in_array($user_login,$tocheck) || current_user_can('manage_options')){

$fep = new fep_main_class();
$token = $fep->fep_create_nonce();
$Pto = ( isset( $_GET['to'] ) ) ? $_GET['to']: '';
if (is_email($Pto)){$to = $Pto;} else { $to = '';}
$to = ( isset( $_REQUEST['fep-send-email-to'] ) ) ? $_REQUEST['fep-send-email-to']: $to;
$domain_name =  preg_replace('/^www\./','',$_SERVER['SERVER_NAME']);
$from = 'noreply@'.$domain_name;
$subject = ( isset( $_REQUEST['fep-send-email-subject'] ) ) ? $_REQUEST['fep-send-email-subject']: '';
$message = ( isset( $_REQUEST['fep-send-email-message'] ) ) ? $_REQUEST['fep-send-email-message']: '';
$message2 = ( isset( $_REQUEST['fep-send-email-message2'] ) ) ? $_REQUEST['fep-send-email-message2']: $this->adminOps['fep_cf_efoot'];

$form =  "<p>
      <form name='fep-send-email' action='' method='post'>
      ".__("To", "fep").":*<br />
      <input type='text' name='fep-send-email-to' placeholder='Email Address' value='$to' /><br/>
	  ".__("From Name", "fep").":*<br />
      <input type='text' name='fep-send-email-from-name' value='".get_bloginfo('name')."' /><br/>
	  ".__("From Email", "fep").":*<br />
      <input type='text' name='fep-send-email-from' value='$from' /><br/>
	  ".__("Subject", "fep").":*<br />
      <input type='text' name='fep-send-email-subject' value='$subject' /><br/>
	  ".__("Message", "fep").":*<br />
      <textarea rows='10' cols='40' name='fep-send-email-message'>$message</textarea><br/>
	  ".__("Footer", "fep").":<br />
      <textarea name='fep-send-email-message2'>$message2</textarea><br/>
	  <input type='hidden' name='token' value='$token' /><br/>
      <input class='button-primary' type='submit' name='fep-send-email' value='".__("Send Email", "fep")."' />
      </form></p>";
	  
	  return $form;
} else {
//does not have manage_options and department username
return "<div id='fep-error'>".__("No permission.", "fep")."</div>";}
}

function send_email_action() 
{
		
if (isset($_POST['fep-send-email'])){
		$fep = new fep_main_class();
		$errors = new WP_Error();
		
$to = $_POST['fep-send-email-to'];
$name = esc_attr($_POST['fep-send-email-from-name']);
$from = $_POST['fep-send-email-from'];
$subject = esc_attr($_POST['fep-send-email-subject']);
$message1 = esc_textarea($_POST['fep-send-email-message']);
$message2 = esc_textarea($_POST['fep-send-email-message2']); 
// message lines should not exceed 70 characters (PHP rule), so wrap it
$message = wordwrap($message1, 70);
$message .= "\r\n\r\n";
$message .= wordwrap($message2, 70);


if (!is_email($to) ) 
$errors->add('noEmail', __("Please enter a valid email address in \"To\" field!", 'fep'));
	  if (!$name ) 
$errors->add('noName', __("Please enter a valid Name in \"From Name\" field!", 'fep'));
	  if (!is_email($from) ) 
$errors->add('invalidEmail', __("Please enter a valid email address in \"From Email\" field!", 'fep'));
	  if (!$subject )
$errors->add('noSubject', __("Please your message Subject in \"Subject\" field!", 'fep'));
	  if (!$message1 ) 
$errors->add('noMessage', __("Please enter your message in \"Message\" field!", 'fep'));
	  
	  $headers = "MIME-Version: 1.0\r\n" .
          "From: ".$name." "."<".$from.">\r\n" .
          "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\r\n";
		
		$postedToken = filter_input(INPUT_POST, 'token');
	  if (empty($postedToken))
      {
        $errors->add('emptyToken', __('Empty Token.', 'fep'));
      }

		if((count($errors->get_error_codes())==0) &&  $fep->fep_verify_nonce($postedToken)){
		  
	  $fepEmail= wp_mail($to, $subject, $message, $headers);
	  if ( !$fepEmail ) {
	  $errors->add('SomeError', __('Something wrong please try again!', 'fep'));
	  	}
	  }
	  
	  return $errors;
	  
}
	}
	
/******************************************SEND EMAIL END******************************************/
		
		function get_ip() {
	// Function to get the client IP address
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    elseif(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    elseif(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    elseif(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    elseif(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

		function isBot() {
	$bots = array("Indy", "Blaiz", "Java", "libwww-perl", "Python", "OutfoxBot", "User-Agent", "PycURL", "AlphaServer", "T8Abot", "Syntryx", "WinHttp", "WebBandit", "nicebot", "Teoma", "alexa", "froogle", "inktomi", "looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory", "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot", "crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz");

	foreach ($bots as $bot)
		if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
			return true;

	if (empty($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] == " ")
		return true;

	return false;
}

		function Error($wp_error){
	if(!is_wp_error($wp_error)){
		return '';
	}
	if(count($wp_error->get_error_messages())==0){
		return '';
	}
	$errors = $wp_error->get_error_messages();
	$html = '<div id="fep-cf-error">';
	foreach($errors as $error){
		$html .= '<strong>' . __('Error', 'fep') . ":</strong> $error<br />";
	}
	$html .= '</div>';
	return $html;
}
	
	function addSettings( $links, $file ) {
	//add settings link in plugins page
	$plugin_file = 'front-end-pm/front-end-pm.php';
	if ( $file == $plugin_file ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=fep-admin-settings' ) . '">' .__( 'Settings', 'fep' ) . '</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}

 function fep_cron_add_weekly( $schedules ) {
 	// Adds once weekly to the existing schedules.
 	$schedules['weekly'] = array(
 		'interval' => 604800,
 		'display' => __( 'Once Weekly' )
 	);
 	return $schedules;
 }
 //Schedule activate when this plugin install
 function schedule_activation() {
	wp_schedule_event( time(), 'weekly', 'fep_weekly_event_hook' );
}
//Schedule deactivate when this plugin uninstall
function schedule_deactivation() {
	wp_clear_scheduled_hook( 'fep_weekly_event_hook' );
}
//Delete spam messages weekly
function fep_weekly_spam_delete()
    {
      global $wpdb;
	  $fep = new fep_main_class();
	  $prev = time()-604800;
	  $prevdate = date("Y-m-d H:i:s", $prev);
	  $spams = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$fep->fepTable} WHERE send_date < %s AND (message_read = 7 OR message_read = 8) ORDER BY id DESC", $prevdate));
	  $spamID = array();
	  foreach ($spams as $spam) {
	  $spamID[] = $spam->id;
	  }
	  $query = implode(",", $spamID);
	  $wpdb->query("DELETE FROM {$fep->cfTable} WHERE message_id IN ({$query})");
	  $wpdb->query($wpdb->prepare("DELETE FROM {$fep->fepTable} WHERE send_date < %s AND (message_read = 7 OR message_read = 8)", $prevdate));
	  
      return;
    }

		}//END CLASS
	}//ENDIF

?>