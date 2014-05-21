<?php
include_once('bbcode.php');
//clFEPm CLASS
if (!class_exists("clFEPm"))
{
  class clFEPm
  {
/******************************************SETUP BEGIN******************************************/
    //Constructor
    function clFEPm()
    {
      $this->setupLinks();
      $this->adminOps = $this->getAdminOps();
    }

    function fepActivate()
    {
      global $wpdb;

      $charset_collate = '';
      if( $wpdb->has_cap('collation'))
      {
        if(!empty($wpdb->charset))
          $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if(!empty($wpdb->collate))
          $charset_collate .= " COLLATE $wpdb->collate";
      }
	  $installed_ver = get_option( "fep_db_version" );
	  $fep_db_version = 1.1;

	if( $installed_ver != $fep_db_version ) {

      $sqlMsgs = 	"CREATE TABLE ".$this->fepTable."(
            `id` int(11) NOT NULL auto_increment,
            `parent_id` int(11) NOT NULL default '0',
            `from_user` int(11) NOT NULL default '0',
            `to_user` int(11) NOT NULL default '0',
            `last_sender` int(11) NOT NULL default '0',
            `date` datetime NOT NULL default '0000-00-00 00:00:00',
            `last_date` datetime NOT NULL default '0000-00-00 00:00:00',
            `message_title` varchar(65) NOT NULL,
            `message_contents` longtext NOT NULL,
            `message_read` int(11) NOT NULL default '0',
            `to_del` int(11) NOT NULL default '0',
            `from_del` int(11) NOT NULL default '0',
            PRIMARY KEY (`id`))
            {$charset_collate};";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

      dbDelta($sqlMsgs);
	  update_option( "fep_db_version", $fep_db_version );
	  }
    }
	
	function translation()
	{
	//SETUP TEXT DOMAIN FOR TRANSLATIONS
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain('fep', false, $plugin_dir.'/languages/');
	}

    function widget($args)
    {
      global $user_ID;
      $uData = get_userdata($user_ID);
	  $this->setPageURLs();
      echo $args['before_widget'];
      if (!$uData)
        echo __("Login to view your messages", "fep");
      else
      {
        $numNew = $this->getNewMsgs_btn();
        $numAnn = $this->getAnnouncementsNum_btn();
		$numNewadm = $this->getNewMsgs_admin();
        echo "<a class='fep-button' href='".$this->pageURL."'>".__("Inbox", "fep")."".$numNew."</a>
		<a class='fep-button' href='".$this->actionURL."viewannouncements'>".__("Announcement", "fep")."".$numAnn."</a>";
		if (current_user_can('manage_options'))
		echo "<a class='fep-button' href='".$this->actionURL."viewallmgs'>".__("Other's Message", "fep")."".$numNewadm."</a>";
      }
      echo $args['after_widget'];
    }
	
	    function widget_text($args)
    {
      global $user_ID;
      $uData = get_userdata($user_ID);
	  $this->setPageURLs();
      echo $args['before_widget'];
      echo $args['before_title'].__("Messages", "fep").$args['after_title'];
      if (!$uData)
        echo __("Login to view your messages", "fep");
      else
      {
        $numNew = $this->getNewMsgs();
        $numAnn = $this->getAnnouncementsNum();
		$numNewadm = $this->getNewMsgs_admin();
        echo __("Hi", "fep")." ".$uData->display_name.",<br/>".
        __("You have", "fep")." <a href='".$this->pageURL."'>(<font color='red'>".$numNew."</font>) ".__("new message(s)", "fep")."</a><br/>".
        __("There are", "fep")." <a href='".$this->actionURL."viewannouncements'>(<font color='red'>".$numAnn."</font>) ".__("announcement(s)", "fep")."</a><br/>";
		if (current_user_can('manage_options'))
		echo "<a href='".$this->actionURL."viewallmgs'>".__("Other's Message(s)", "fep")."".$numNewadm."</a><br/>";
        echo "<a href='".$this->pageURL."'>".__("View Message Box", "fep")."</a><br/>";
		
      } 
      echo $args['after_widget'];
    }

    //Setup some variables
    var $adminOpsName = "FEP_options";
    var $adminOps = array();
    var $userOpsName = "FEP_uOptions";
    var $userOps = array();

    var $error = "";
	var $success = "";

    var $pluginDir = "";
    var $pluginURL = "";
    var $styleDir = "";
    var $styleURL = "";
    var $pageURL = "";
    var $actionURL = "";
    var $jsURL = "";

    var $fepTable = "";

    function jsInit()
    {
	if (isset($_GET['fepjscript']))
      if($_GET['fepjscript'] == '1')
      {
        global $wpdb, $user_ID;
        require_once('js/search.php');
      }
    }

    function setupLinks() //And DB table name too :)
    {
      global $wpdb;
      $this->pluginDir = plugin_dir_path( __FILE__ )."/";
      $this->pluginURL = plugins_url()."/front-end-pm/";
      $this->styleDir = $this->pluginDir."style/";
      $this->styleURL = $this->pluginURL."style/";
      $this->jsURL = $this->pluginURL."js/";

      $this->fepTable = $wpdb->prefix."fep_messages";
    }

    function fep_enqueue_scripts()
    {
	wp_enqueue_style( 'fep-style', $this->styleURL . 'style.css' );
	wp_enqueue_script( 'fep-script', $this->jsURL . 'script.js', array(), '1.0.0', true );
    }

    function getPageID()
    {
      global $wpdb;
      return $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%[front-end-pm]%' AND post_status = 'publish' AND post_type = 'page' LIMIT 1");
    }

    function setPageURLs()
    {
      global $wp_rewrite;
      if($wp_rewrite->using_permalinks())
        $delim = "?";
      else
        $delim = "&";
      $this->pageURL = get_permalink($this->getPageID());
      $this->actionURL = $this->pageURL.$delim."fepaction=";
    }
/******************************************SETUP END******************************************/

/******************************************ADMIN SETTINGS PAGE BEGIN******************************************/
    function addAdminPage()
    {
	  add_menu_page('Front End PM', 'Front End PM', 'manage_options', 'fep-admin-settings', array(&$this, "dispAdminPage"),plugins_url( 'front-end-pm/images/msgBox.gif' ));
	add_submenu_page('fep-admin-settings', 'Front End PM - ' .__('Settings','cp'), __('Settings','cp'), 'manage_options', 'fep-admin-settings', array(&$this, "dispAdminPage"));
	add_submenu_page('fep-admin-settings', 'Front End PM - ' .__('Instruction','cp'), __('Instruction','cp'), 'manage_options', 'fep-instruction', array(&$this, "dispInstructionPage"));
    }

    function dispAdminPage()
    {
      if ($this->pmAdminSave())
        echo "<div id='message' class='updated fade'><p>".__("Options successfully saved", "fep")."</p></div>";
      $viewAdminOps = $this->getAdminOps(); //Get current options
	  $url = 'http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/';
      echo 	"<div class='wrap'>
          <h2>".__("Front End PM Settings", "fep")."</h2>
		<form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='_top'>
		<input type='hidden' name='cmd' value='_donations'>
		<input type='hidden' name='business' value='4HKBQ3QFSCPHJ'>
		<input type='hidden' name='lc' value='US'>
		<input type='hidden' name='item_name' value='Front End PM'>
		<input type='hidden' name='item_number' value='Front End PM'>
		<input type='hidden' name='currency_code' value='USD'>
		<input type='hidden' name='bn' value='PP-DonationsBF:btn_donateCC_LG.gif:NonHosted'>
		<input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'>
		<img alt='' border='0' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' width='1' height='1'>
		</form>
          <form id='fep-admin-save-form' name='fep-admin-save-form' method='post' action=''>
          <table class='widefat'>
          <thead>
          <tr><th width='30%'>".__("Setting", "fep")."</th><th width='70%'>".__("Value", "fep")."</th></tr>
          </thead>
          <tr><td>".__("Max messages a user can keep in box? (0 = Unlimited)", "fep")."<br /><small>".__("Admins always have Unlimited", "fep")."</small></td><td><input type='text' size='10' name='num_messages' value='".$viewAdminOps['num_messages']."' /><br/> ".__("Default","fep").": 50</td></tr>
          <tr><td>".__("Messages to show per page", "fep")."<br/><small>".__("Do not set this to 0!", "fep")."</small></td><td><input type='text' size='10' name='messages_page' value='".$viewAdminOps['messages_page']."' /><br/> ".__("Default","fep").": 15</td></tr>
		  <tr><td>".__("Maximum user per page in Directory", "fep")."<br/><small>".__("Do not set this to 0!", "fep")."</small></td><td><input type='text' size='10' name='user_page' value='".$viewAdminOps['user_page']."' /><br/> ".__("Default","fep").": 50</td></tr>
		  <tr><td>".__("Time delay between two messages send by a user in minutes (0 = No delay required)", "fep")."<br/><small>".__("Admins have no restriction", "fep")."</small></td><td><input type='text' size='10' name='time_delay' value='".$viewAdminOps['time_delay']."' /><br/> ".__("Default","fep").": 5</td></tr>
		  <tr><td>".__("Block Username", "fep")."<br /><small>".__("Separated by comma", "fep")."</small></td><td><input type='text' size='30' name='have_permission' value='".$viewAdminOps['have_permission']."' /></td></tr>
		  <tr><td>".__("Valid email address for \"to\" field of announcement email", "fep")."<br /><small>".__("All users email will be in \"Bcc\" field", "fep")."</small></td><td><input type='text' size='30' name='ann_to' value='".$viewAdminOps['ann_to']."' /></td></tr>
		  <tr><td colspan='2'><input type='checkbox' name='notify_ann' ".checked($viewAdminOps['notify_ann'], 'on', false)." /> ".__("Send email to all users when a new announcement is published?", "fep")."</td></tr>
		  <tr><td colspan='2'><input type='checkbox' name='hide_directory' ".checked($viewAdminOps['hide_directory'], 'on', false)." /> ".__("Hide Directory from front end?", "fep")."<br /><small>".__("Always shown to Admins", "fep")."</small></td></tr>
		  <tr><td colspan='2'><input type='checkbox' name='hide_autosuggest' ".checked($viewAdminOps['hide_autosuggest'], 'on', false)." /> ".__("Hide Autosuggestion when typing recipient name?", "fep")."<br /><small>".__("Always shown to Admins", "fep")."</small></td></tr>
		  <tr><td colspan='2'><input type='checkbox' name='disable_new' ".checked($viewAdminOps['disable_new'], 'on', false)." /> ".__("Disable \"send new message\" for all users except admins?", "fep")."<br /><small>".__("Users can send reply", "fep")."</small></td></tr>
          <tr><td colspan='2'><input type='checkbox' name='hide_branding' ".checked($viewAdminOps['hide_branding'], 'on', false)." /> ".__("Hide Branding Footer?", "fep")."</td></tr>
          <tr><td colspan='2'><span><input class='button-primary' type='submit' name='fep-admin-save' value='".__("Save Options", "fep")."' /></span></td></tr>
          </table>
		  </form>
		  <ul>".sprintf(__("For more info or report bug pleasse visit <a href='%s' target='_blank'>Front End PM</a>", "fep"),esc_url($url))."</ul>
          </div>";
    }
	
	function dispInstructionPage()
	{
	$url = 'http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/';
	echo 	"<div class='wrap'>
          <h2>".__("Front End PM Setup Instruction", "fep")."</h2>
          <p><ul><li>".__("Create a new page.", "fep")."</li>
          <li>".__("Paste following code under the HTML tab of the page editor", "fep")."<code>[front-end-pm]</code></li>
          <li>".__("Publish the page.", "fep")."</li>
		  <li>".__("Or you can create a page below.", "fep")."</li>
		  <li>".sprintf(__("For more info or report bug pleasse visit <a href='%s' target='_blank'>Front End PM</a>", "fep"),esc_url($url))."</li>
          </ul></p>
		  <h2>".__("Create Page For \"Front End PM\"", "fep")."</h2>
		  ".$this->fep_createPage()."</div>";
		  }

    function pmAdminSave()
    {
      if (isset($_POST['fep-admin-save']))
      {
	  if (!is_email($_POST['ann_to'])) {
	  echo "<div id='message' class='error'><p>".__("Please enter a valid email address!", "fep")."</p></div>";
	  return;}
	  if (!ctype_digit($_POST['num_messages']) || !$this->is_positive($_POST['messages_page']) || !$this->is_positive($_POST['user_page']) || !ctype_digit($_POST['time_delay'])) {
	  echo "<div id='message' class='error'><p>".__("First four fields support only positive numbers!", "fep")."</p></div>"; 
	  return;}
        $saveAdminOps = array('num_messages' 	=> $_POST['num_messages'],
                              'messages_page' => $_POST['messages_page'],
							  'user_page' => $_POST['user_page'],
							  'time_delay' => $_POST['time_delay'],
                              'hide_branding' => $_POST['hide_branding'],
							  'hide_directory' => $_POST['hide_directory'],
							  'hide_autosuggest' => $_POST['hide_autosuggest'],
							  'disable_new' => $_POST['disable_new'],
							  'ann_to' => $_POST['ann_to'],
							  'notify_ann' => $_POST['notify_ann'],
							  'have_permission' => $_POST['have_permission']
        );
        update_option($this->adminOpsName, $saveAdminOps);
        return true;
      }
      return false;
    }

    function getAdminOps()
    {
      $pmAdminOps = array('num_messages' => 50,
                          'messages_page' => 15,
						  'user_page' => 50,
						  'time_delay' => 5,
						  'hide_directory' => false,
						  'ann_to' => get_bloginfo("admin_email"),
						  'notify_ann' => false,
						  'hide_autosuggest' => false,
						  'disable_new' => false,
                          'hide_branding' => false,
						  'have_permission' => ''
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
	
	function fep_createPage(){
	$token = $this->getToken();
	$form = "<p>
      <form name='fep-create-page' action='".$this->fep_createPage_action()."' method='post'>
      ".__("Title of \"Front End PM\" Page", "fep").":<br/>
      <input type='text' name='fep-create-page-title' value='' /><br/>
	  <strong>".__("Slug", "fep")."</strong>: <em>".__("If blank, slug will be automatically created based on Title", "fep")."</em><br/>
      <input type='text' name='fep-create-page-slug' value='' /><br/>
	  <input type='hidden' name='token' value='".$token."' /><br/>
      <input class='button-primary' type='submit' name='fep-create-page' value='".__("Create Page", "fep")."' />
      </form></p>";

      return $form;
    }

	function fep_createPage_action(){
	if (isset($_POST['fep-create-page'])){
      	$titlePre = wp_strip_all_tags($_POST['fep-create-page-title']);
		$title = utf8_encode($titlePre);
		$slugPre = wp_strip_all_tags($_POST['fep-create-page-slug']);
		$slug = utf8_encode($slugPre);
		
		if ($this->getPageID() !=''){
		echo "<div id='message' class='error'><p>" .sprintf(__("Already created page <a href='%s'>%s </a> for \"Front End PM\". Please use that page instead!", "fep"),get_permalink($this->getPageID()),get_the_title($this->getPageID()))."</p></div>";
        return;}
		if (!$title){
          echo "<div id='message' class='error'><p>" .__("You must enter a valid Title!", "fep")."</p></div>";
        return;}
		// Check if a form has been sent
		$postedToken = filter_input(INPUT_POST, 'token');
	  	if (empty($postedToken))
     	 {
	 	 echo "<div id='message' class='error'><p>" .__("Invalid Token. Please try again!", "fep")."</p></div>";
        return;
      	}
  		if(!$this->isTokenValid($postedToken)){
    	// Actually This is not first form submission. First Submission Pass this condition and inserted into db.
		echo "<div id='message' class='updated'><p>" .__("Page for \"Front End PM\" successfully created!", "fep")."</p></div>";
        return;
		}
		
		$fep_page = array(
  		'post_title'    => $title,
		'post_name'    => $slug,
  		'post_content'  => '[front-end-pm]',
  		'post_status'   => 'publish',
  		'post_type' => 'page'
		);
	$pageID = wp_insert_post( $fep_page );
	if($pageID == 0){
	echo "<div id='message' class='error'><p>" .__("Something wrong.Please try again to create page!", "fep")."</p></div>";
        return;
		} else {
		echo "<div id='message' class='updated'><p>" .sprintf(__("Page <a href='%s'>%s </a> for \"Front End PM\" successfully created!", "fep"),get_permalink($pageID),get_the_title($pageID))."</p></div>";
        return;}
		
		}
	}
/******************************************ADMIN SETTINGS PAGE END******************************************/

/******************************************USER SETTINGS PAGE BEGIN******************************************/
    function dispUserPage()
    {
      global $user_ID;
      if ($this->pmUserSave())
        $this->success = __("Your settings have been saved!", "fep");
      $viewUserOps = $this->getUserOps($user_ID); //Get current options
      $prefs = "<p><strong>".__("Set your preferences below", "fep").":</strong></p>
      <form id='fep-user-save-form' name='fep-user-save-form' method='post' action=''>
      <input type='checkbox' name='allow_messages' value='true'";
      if($viewUserOps['allow_messages'] == 'true')
        $prefs .= "checked='checked'";
      $prefs .= "/> <i>".__("Allow others to send me messages?", "fep")."</i><br/>

      <input type='checkbox' name='allow_emails' value='true'";
      if($viewUserOps['allow_emails'] == 'true')
        $prefs .= "checked='checked'";
      $prefs .= "/> <i>".__("Email me when I get new messages?", "fep")."</i><br/>
	  
	  <input type='checkbox' name='allow_ann' value='true'";
      if($viewUserOps['allow_ann'] == 'true')
        $prefs .= "checked='checked'";
      $prefs .= "/> <i>".__("Email me when New announcement is published?", "fep")."</i><br/>
	  
      <input class='button' type='submit' name='fep-user-save' value='".__("Save Options", "fep")."' />
      </form>";
      return $prefs;
    }

    function pmUserSave()
    {
      global $user_ID;
      if (isset($_POST['fep-user-save']))
      {
        $saveUserOps = array(	'allow_emails' 	=> $_POST['allow_emails'],
                    'allow_messages' => $_POST['allow_messages'],
					'allow_ann' => $_POST['allow_ann']
        );
        update_user_meta($user_ID, $this->userOpsName, $saveUserOps);
        return true;
      }
      return false;
    }

    function getUserOps($ID)
    {
      $pmUserOps = array(	'allow_emails' 		=> 'true',
                'allow_messages' 	=> 'true',
				'allow_ann' 	=> 'true'
      );

      //Get old values if they exist
      $userOps = get_user_meta($ID, $this->userOpsName, true);
      if (!empty($userOps))
      {
        foreach ($userOps as $key => $option)
          $pmUserOps[$key] = $option;
      }

      update_user_meta($ID, $this->userOpsName, $pmUserOps);
      return $pmUserOps;
    }
/******************************************USER SETTINGS PAGE END******************************************/

/******************************************NEW MESSAGE PAGE BEGIN******************************************/
    function dispNewMsg()
    {
      global $user_ID;
	  $token = $this->getToken();
      $adminOps = $this->getAdminOps();
	  if (isset($_GET['to'])){
      $to = $_GET['to'];
	  }else{ $to = '';}
		if (!$this->have_permission())
		{
        $this->error = __("You cannot send messages because you are blocked by administrator!", "fep");
        return;
      }
	  if ($this->adminOps['disable_new'] == 'on' && !current_user_can('manage_options'))
		{
        $this->error = __("Send new message is disabled for users!", "fep");
        return;
      }
      if (!$this->isBoxFull($user_ID, $adminOps['num_messages'], '1'))
      {
	$message_to = ( isset( $_REQUEST['message_to'] ) ) ? $_REQUEST['message_to']: '';
	$message_top = ( isset( $_REQUEST['message_top'] ) ) ? $_REQUEST['message_top']: '';
	$message_title = ( isset( $_REQUEST['message_title'] ) ) ? $_REQUEST['message_title']: '';
	$message_content = ( isset( $_REQUEST['message_content'] ) ) ? $_REQUEST['message_content']: '';
	
        $newMsg = "<p><strong>".__("Create New Message", "fep").":</strong></p>";
        $newMsg .= "<form name='message' action='".$this->actionURL."checkmessage' method='post'>".
        __("To", "fep")."<font color='red'>*</font>: ";
		if($this->adminOps['hide_autosuggest'] != 'on' || current_user_can('manage_options')) { 
		$newMsg .="<noscript>Username of recipient</noscript><br/>";
        $newMsg .="<input type='hidden' id='search-qq' name='message_to' autocomplete='off' value='".$this->convertToUser($to)."".$message_to."' />
		<input type='text' id='search-q' onkeyup='javascript:autosuggest(\"".$this->actionURL."\")' name='message_top' placeholder='Name of recipient' autocomplete='off' value='".$this->convertToDisplay($to)."".$message_top."' /><br/>
        <div id='result'></div>";
		} else {
		$newMsg .="<br/><input type='text' name='message_to' placeholder='Username of recipient' autocomplete='off' value='".$this->convertToUser($to)."".$message_to."' /><br/>";}
		
        $newMsg .= __("Subject", "fep")."<font color='red'>*</font>:<br/>
        <input type='text' name='message_title' placeholder='Subject' maxlength='65' value='".$message_title."' /><br/>".
        __("Message", "fep")."<font color='red'>*</font>:<br/>".$this->get_form_buttons()."<br/>
        <textarea name='message_content' placeholder='Message Content'>".$message_content."</textarea>
        <input type='hidden' name='message_from' value='".$user_ID."' />
        <input type='hidden' name='parent_id' value='0' />
		<input type='hidden' name='token' value='".$token."' /><br/>
        <input type='submit' id='submit' value='".__("Send Message", "fep")."' />
        </form>";
        
        return $newMsg;
      }
      else
      {
        $this->error = __("You cannot send messages because your message box is full! Please delete some messages.", "fep");
        return;
      }
    }
/******************************************NEW MESSAGE PAGE END******************************************/

/******************************************READ MESSAGE PAGE BEGIN******************************************/
    function dispReadMsg()
    {
      global $wpdb, $user_ID;

      $pID = $_GET['id'];
      $wholeThread = $this->getWholeThread($pID);
	  $token = $this->getToken();

      $threadOut = "<p><strong>".__("Message Thread", "fep").":</strong></p>
      <table><tr><th width='15%'>".__("Sender", "fep")."</th><th width='85%'>".__("Message", "fep")."</th></tr>";

      foreach ($wholeThread as $post)
      {
        //Check for privacy errors first
        if ($post->to_user != $user_ID && $post->from_user != $user_ID && !current_user_can( 'manage_options' ))
        {
          $this->error = __("You do not have permission to view this message!", "fep");
          return;
        }

        //setup info for the reply form
        if ($post->parent_id == 0) //If it is the parent message
        {
          $to = $post->from_user;
          if ($to == $user_ID) //Make sure user doesn't send a message to himself
            $to = $post->to_user;
          $message_title = $this->output_filter($post->message_title);
          if (substr_count($message_title, __("Re:", "fep")) < 1) //Prevent all the Re:'s from happening
            $re = __("Re:", "fep");
          else
            $re = "";
        }

        $uData = get_userdata($post->from_user);
        $threadOut .= "<tr><td><a href='".get_author_posts_url( $uData->ID )."'>".$uData->display_name."</a><br/><small>".$this->formatDate($post->date)."</small><br/>".get_avatar($post->from_user, 60)."</td>";

        if ($post->parent_id == 0) //If it is the parent message
        {
          $threadOut .= "<td class='pmtext'><strong>".__("Subject", "fep").": </strong>".$this->output_filter($post->message_title)."<hr/>".apply_filters("comment_text", $this->autoembed($this->output_filter($post->message_contents)))."</td></tr>";
        }
        else
        {
          $threadOut .= "<td class='pmtext'>".apply_filters("comment_text", $this->autoembed($this->output_filter($post->message_contents)))."</td></tr>";
        }
      }

      $threadOut .= "</table>";

      //SHOW THE REPLY FORM
	  if ($this->have_permission()){
      $threadOut .= "
      <p><strong>".__("Add Reply", "fep").":</strong></p>
      <form name='message' action='".$this->actionURL."checkmessage' method='post'>".
      $this->get_form_buttons()."<br/>
      <textarea name='message_content'></textarea>
      <input type='hidden' name='message_to' value='".get_userdata($to)->user_login."' />
	  <input type='hidden' name='message_top' value='".get_userdata($to)->display_name."' />
      <input type='hidden' name='message_title' value='".$re.$message_title."' />
      <input type='hidden' name='message_from' value='".$user_ID."' />
      <input type='hidden' name='parent_id' value='".$pID."' />
	  <input type='hidden' name='token' value='".$token."' /><br/>
      <input type='submit' value='".__("Send Message", "fep")."' />
      </form>";
	  } else {
        $this->error = __("You cannot send messages because you are blocked by administrator!", "fep");
      }

      if ($user_ID != $post->from_user) //Update only if the reader is not the sender ???
        $wpdb->query($wpdb->prepare("UPDATE {$this->fepTable} SET message_read = 1 WHERE id = %d", $pID));

      return $threadOut;
    }
	
	function dispReadMsg_admin()
    {
      global $wpdb, $user_ID;

      $pID = $_GET['id'];
      $wholeThread = $this->getWholeThread($pID);
	  $token = $this->getToken();

      $threadOut = "<p><strong>".__("Message Thread", "fep").":</strong></p>
      <table><tr><th width='15%'>".__("Sender", "fep")."</th><th width='85%'>".__("Message", "fep")."</th></tr>";

      foreach ($wholeThread as $post)
      {
        //Check for privacy errors first
        if (!current_user_can( 'manage_options' ))
        {
          $this->error = __("You do not have permission to view this message!", "fep");
          return;
        }

        //setup info for the reply form
        if ($post->parent_id == 0) //If it is the parent message
        {
          $to = $post->from_user;
          if ($to == $user_ID) //Make sure user doesn't send a message to himself
            $to = $post->to_user;
          $message_title = $this->output_filter($post->message_title);
          if (substr_count($message_title, __("Re:", "fep")) < 1) //Prevent all the Re:'s from happening
            $re = __("Re:", "fep");
          else
            $re = "";
        }

        $uData = get_userdata($post->from_user);
        $threadOut .= "<tr><td><a href='".get_author_posts_url( $uData->ID )."'>".$uData->display_name."</a><br/><small>".$this->formatDate($post->date)."</small><br/>".get_avatar($post->from_user, 60)."</td>";

        if ($post->parent_id == 0) //If it is the parent message
        {
          $threadOut .= "<td class='pmtext'><strong>".__("Subject", "fep").": </strong>".$this->output_filter($post->message_title)."<hr/>".apply_filters("comment_text", $this->autoembed($this->output_filter($post->message_contents)))."</td></tr>";
        }
        else
        {
          $threadOut .= "<td class='pmtext'>".apply_filters("comment_text", $this->autoembed($this->output_filter($post->message_contents)))."</td></tr>";
        }
      }

      //SHOW THE REPLY FORM
      $threadOut .= "</table>
      <p><strong>".__("Add Reply", "fep").":</strong></p>
      <form name='message' action='".$this->actionURL."checkmessage' method='post'>".
      $this->get_form_buttons()."<br/>
      <textarea name='message_content'></textarea>
      <input type='hidden' name='message_to' value='".get_userdata($to)->user_login."' />
	  <input type='hidden' name='message_top' value='".get_userdata($to)->display_name."' />
      <input type='hidden' name='message_title' value='".$re.$message_title."' />
      <input type='hidden' name='message_from' value='".$user_ID."' />
      <input type='hidden' name='parent_id' value='".$pID."' />
	  <input type='hidden' name='token' value='".$token."' /><br/>
      <input type='submit' value='".__("Send Message", "fep")."' />
      </form>";

      return $threadOut;
    }

    function getWholeThread($id)
    {
      global $wpdb;
      $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->fepTable} WHERE id = %d OR parent_id = %d ORDER BY id ASC", $id, $id));
      return $results;
    }
	
    function getInfo($id)
    {
      global $wpdb;
      $to = $wpdb->get_var($wpdb->prepare("SELECT to_user FROM {$this->fepTable} WHERE id = %d", $id));
	  $from = $wpdb->get_var($wpdb->prepare("SELECT from_user FROM {$this->fepTable} WHERE id = %d", $id));
      return array ( 'to' => $to , 'from' => $from );
    }

    function convertToUser($to)
    {
      $user = get_user_by( 'login' , $to );
	  $result = $user->user_login;
      return $result;
    }
	function convertToDisplay($to)
    {
	$user = get_user_by( 'login' , $to );
	$result = $user->display_name;
      return $result;
    }
/******************************************READ MESSAGE PAGE END******************************************/

/******************************************CHECK MESSAGE PAGE BEGIN******************************************/
    function dispCheckMsg()
    {
      global $wpdb, $user_ID;
      $from = $_POST['message_from'];
      if ($_POST['message_to']) {
	  $preTo = $_POST['message_to'];
	  } else {
	  $preTo = $_POST['message_top']; }
      $to = $this->convertToID($preTo);
      $title = $this->input_filter($_POST['message_title']);
      $content = $this->input_filter($_POST['message_content']);
      $parentID = $_POST['parent_id'];
      $date = current_time('mysql');
      
      $adminOps = $this->getAdminOps();
      if ($to)
        $toUserOps = $this->getUserOps($to);

      //Check for errors first
      if (!$to || !$title || !$content || ($from != $user_ID))
      {
        if (!$to)
          $theError = __("You must enter a valid recipient!", "fep");
        if (!$title)
          $theError = __("You must enter a valid subject!", "fep");
        if (!$content)
          $theError = __("You must enter some message content!", "fep");
        if ($from != $user_ID)
          $theError = __("You do not have permission to send this message!", "fep");
        $this->error = $theError;
        return $this->dispNewMsg();
      }
      if ($toUserOps['allow_messages'] != 'true')
      {
        $this->error = __("This user does not want to receive messages!", "fep");
        return;
      }
      if ($this->isBoxFull($to, $adminOps['num_messages'], $parentID))
      {
        $this->error = __("Your or Recipients Message Box Is Full!", "fep");
        return;
      }
	  if (!$this->have_permission())
      {
        $this->error = __("You cannot send messages because you are blocked by administrator!", "fep");
        return;
      }
	  $timeDelay = $this->TimeDelay($adminOps['time_delay']);
	  if ($timeDelay['diffr'] < $adminOps['time_delay'] && !current_user_can('manage_options'))
      {
        $this->error = sprintf(__("Please wait at least more %s to send another message!", "fep"),$timeDelay['time']);
        return;
      }
	  if ($parentID != 0) {
	  $mgsInfo = $this->getInfo($parentID);
	  if ($mgsInfo['to'] != $user_ID && $mgsInfo['from'] != $user_ID && !current_user_can( 'manage_options' ))
        {
          $this->error = __("You do not have permission to send this message!", "fep");
          return;
        }
		}
	  // Check if a form has been sent
		$postedToken = filter_input(INPUT_POST, 'token');
	  if (empty($postedToken))
      {
        $this->error = __("Invalid Token. Please try again!", "fep");
        return;
      }
  		if(!$this->isTokenValid($postedToken)){
    // Actually This is not first form submission. First Submission Pass this condition and inserted into db.
	$this->success = __("Your message was successfully sent!", "fep");
        return;
		}

      //If no errors then continue on
      if ($parentID == 0)
        $wpdb->query($wpdb->prepare("INSERT INTO {$this->fepTable} (from_user, to_user, message_title, message_contents, parent_id, last_sender, date, last_date) VALUES ( %d, %d, %s, %s, %d, %d, %s, %s )", $from, $to, $title, $content, $parentID, $from, $date, $date));
      else
      {
        $wpdb->query($wpdb->prepare("INSERT INTO {$this->fepTable} (from_user, to_user, message_title, message_contents, parent_id, date) VALUES ( %d, %d, %s, %s, %d, %s)", $from, $to, $title, $content, $parentID, $date));
        $wpdb->query($wpdb->prepare("UPDATE {$this->fepTable} SET message_read = 0,last_sender = %d,last_date = %s, to_del = 0, from_del = 0 WHERE id = %d", $from, $date, $parentID));
      }

      $this->success = __("Your message was successfully sent!", "fep");

      $this->sendEmail($to, $from, $title);

      return;
    }

    function isBoxFull($to, $boxSize, $parentID)
    {
      global $wpdb;

      $get_messages = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE (to_user = %d AND parent_id = 0 AND to_del <> 1) OR (from_user = %d AND parent_id = 0 AND from_del <> 1)", $to, $to));
      $num = $wpdb->num_rows;

      if ($boxSize == 0 || $num < $boxSize || $parentID != 0 || current_user_can('manage_options') || user_can( $to, 'manage_options' ))
        return false;
      else
        return true;
    }

    function sendEmail($to, $from, $title)
    {
      $toOptions = $this->getUserOps($to);
      $notify = $toOptions['allow_emails'];
      if ($notify == 'true')
      {
        $sendername = get_bloginfo("name");
        $sendermail = get_bloginfo("admin_email");
        $uData = get_userdata($from);
        $sendfrom = $uData->display_name;
        $headers = "MIME-Version: 1.0\r\n" .
          "From: ".$sendername." "."<".$sendermail.">\r\n" . 
          "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\r\n";
		$subject = "" . get_bloginfo("name").": New Message";
		$message = "You have received a new message in \r\n";
		$message .= get_bloginfo("name")."\r\n";
		$message .= "From: ".$sendfrom. "\r\n";
		$message .= "Subject: ".$title. "\r\n";
		$message .= "Please Click the following link to view full Message. \r\n";
		$message .= $this->pageURL."\r\n";		
        $mUser = get_userdata($to);
        $mailTo = $mUser->user_email;
        wp_mail($mailTo, $subject, $message);
      }
    }

    function convertToID($preTo)
    {
      global $user_ID;
		$user = get_user_by( 'login' , $preTo );
		$result = $user->ID;
      if ($result != $user_ID && $result)
        return $result;
      else
        return 0;
    }
/******************************************CHECK MESSAGE PAGE END******************************************/

/******************************************MESSAGE-BOX PAGE BEGIN******************************************/
    function dispMsgBox()
    {
      global $wpdb, $user_ID;

      $adminOps = $this->getAdminOps();
      $numMsgs = $this->getUserNumMsgs();
      if ($numMsgs)
      {
        $msgsOut = "<p><strong>".__("Your Messages", "fep").":</strong></p>";
        $numPgs = $numMsgs / $adminOps['messages_page'];
        if ($numPgs > 1)
        {
          $msgsOut .= "<p><strong>".__("Page", "fep").": </strong> ";
          for ($i = 0; $i < $numPgs; $i++)
            if ($_GET['pmpage'] != $i)
              $msgsOut .= "<a href='".$this->actionURL."messagebox&pmpage=".$i."'>".($i+1)."</a> ";
            else
              $msgsOut .= "[<b>".($i+1)."</b>] ";
          $msgsOut .= "</p>";
        }

        $msgsOut .= "<table><tr class='head'>
        <th width='20%'>".__("Started By", "fep")."</th>
		<th width='20%'>".__("To", "fep")."</th>
        <th width='30%'>".__("Subject", "fep")."</th>
        <th width='20%'>".__("Last Reply By", "fep")."</th>
        <th width='10%'>".__("Delete", "fep")."</th></tr>";
        $msgs = $this->getMsgs();
		$a = 0;
        foreach ($msgs as $msg)
        {
          if ($msg->message_read == 0 && $msg->last_sender != $user_ID)
            $read = "<font color='#FF0000'>".__("Unread", "fep")."</font>";
          else
            $read = __("Read", "fep");
          $uSend = get_userdata($msg->from_user);
          $uLast = get_userdata($msg->last_sender);
          $toUser = get_userdata($msg->to_user);
		  $msgsOut .= "<tr class='trodd".$a."'>";
		  if ($uSend->ID != $user_ID){
          $msgsOut .= "<td><a href='".get_author_posts_url( $uSend->ID )."'>" .$uSend->display_name. "</a><br/><small>".$this->formatDate($msg->date)."</small></td>"; }
		  else {
		  $msgsOut .= "<td>" .$uSend->display_name. "<br/><small>".$this->formatDate($msg->date)."</small></td>"; }
		  if ($toUser->ID != $user_ID){
          $msgsOut .= "<td><a href='".get_author_posts_url( $toUser->ID )."'>" .$toUser->display_name. "</a></td>";}
		  else {
		  $msgsOut .= "<td>" .$toUser->display_name. "</td>";}
		  $msgsOut .= "<td><a href='".$this->actionURL."viewmessage&id=".$msg->id."'>".$this->output_filter($msg->message_title)."</a><br/><small>".$read."</small></td>";
		  $msgsOut .= "<td>" .$uLast->display_name. "<br/><small>".$this->formatDate($msg->last_date)."</small></td>";
          $msgsOut .= "<td><a href='".$this->actionURL."deletemessage&id=".$msg->id."' onclick='return confirm(\"".__('Are you sure?', 'fep')."\");'>".__("Delete", "fep")."</a></td>
          </tr>";
		   //Alternate table colors
		  if ($a) $a = 0; else $a = 1;
        }
        $msgsOut .= "</table>";

        return $msgsOut;
      }
      else
      {
        $this->error = __("Your message box is empty!", "fep");
        return;
      }
    }
	
	function getUserNumMsgs_admin()
    {
      global $wpdb, $user_ID;
	  
      $get_messages = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE to_user <> %d AND from_user <> %d AND message_read <> 2 AND parent_id = 0", $user_ID, $user_ID));
      $num = $wpdb->num_rows;
      return $num;
    }
	    function dispMsgBox_admin()
    {
      global $wpdb, $user_ID;

      $adminOps = $this->getAdminOps();
      $numMsgs = $this->getUserNumMsgs_admin();
      if ($numMsgs)
      {
        $msgsOut = "<p><strong>".__("All Messages", "fep").":</strong></p>";
        $numPgs = $numMsgs / $adminOps['messages_page'];
        if ($numPgs > 1)
        {
          $msgsOut .= "<p><strong>".__("Page", "fep").": </strong> ";
          for ($i = 0; $i < $numPgs; $i++)
            if ($_GET['apmpage'] != $i)
              $msgsOut .= "<a href='".$this->actionURL."viewallmgs&apmpage=".$i."'>".($i+1)."</a> ";
            else
              $msgsOut .= "[<b>".($i+1)."</b>] ";
          $msgsOut .= "</p>";
        }

        $msgsOut .= "<table><tr class='head'>
        <th width='20%'>".__("Started By", "fep")."</th>
        <th width='20%'>".__("To", "fep")."</th>
        <th width='30%'>".__("Subject", "fep")."</th>
        <th width='20%'>".__("Last Reply By", "fep")."</th>
        <th width='10%'>".__("Delete", "fep")."</th></tr>";
        $msgs = $this->getMsgs_admin();
		$a = 0;
        foreach ($msgs as $msg)
        {
          if ($msg->message_read == 0 && $msg->last_sender != $user_ID)
            $read = "<font color='#FF0000'>".__("Unread", "fep")."</font>";
          else
            $read = __("Read", "fep");
          $uSend = get_userdata($msg->from_user);
          $uLast = get_userdata($msg->last_sender);
          $toUser = get_userdata($msg->to_user);
		  $msgsOut .= "<tr class='trodd".$a."'>";
		  $msgsOut .= "<td><a href='".get_author_posts_url( $uSend->ID )."'>" .$uSend->display_name. "</a><br/><small>".$this->formatDate($msg->date)."</small></td>";
          $msgsOut .= "<td><a href='".get_author_posts_url( $toUser->ID )."'>" .$toUser->display_name. "</a></td>";
		  $msgsOut .= "<td><a href='".$this->actionURL."viewmessageadmin&id=".$msg->id."'>".$this->output_filter($msg->message_title)."</a><br/><small>".$read."</small></td>";
		  $msgsOut .= "<td>" .$uLast->display_name. "<br/><small>".$this->formatDate($msg->last_date)."</small></td>";
          $msgsOut .= "<td><a href='".$this->actionURL."deletemessageadmin&id=".$msg->id."' onclick='return confirm(\"".__('Are you sure?', 'fep')."\");'>".__("Delete", "fep")."</a></td>
          </tr>";
		  //Alternate table colors
		  if ($a) $a = 0; else $a = 1;
        }
        $msgsOut .= "</table>";

        return $msgsOut;
      }
      else
      {
        $this->error = __("Message box is empty!", "fep");
        return;
      }
    }

    function getMsgs()
    {
      global $wpdb, $user_ID;
	  if (isset($_GET['pmpage'])){
      $page = $_GET['pmpage'];
	  }else{$page = 0;}
      $adminOps = $this->getAdminOps();
      $start = $page * $adminOps['messages_page'];
      $end = $adminOps['messages_page'];

      $get_messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->fepTable} WHERE (to_user = %d AND parent_id = 0 AND to_del <> 1) OR (from_user = %d AND parent_id = 0 AND from_del <> 1) ORDER BY last_date DESC LIMIT %d, %d", $user_ID, $user_ID, $start, $end));

      return $get_messages;
    }
	
	function getMsgs_admin()
    {
      global $wpdb, $user_ID;
	  if (isset($_GET['apmpage'])){
      $page = $_GET['apmpage'];
	  }else{$page = 0;}
      $adminOps = $this->getAdminOps();
      $start = $page * $adminOps['messages_page'];
      $end = $adminOps['messages_page'];
		$get_messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->fepTable} WHERE to_user <> %d AND from_user <> %d AND parent_id = 0 AND message_read <> 2 ORDER BY last_date DESC LIMIT %d, %d", $user_ID, $user_ID, $start, $end));

      return $get_messages;
    }
/******************************************MESSAGE-BOX PAGE END******************************************/

/******************************************DELETE PAGE BEGIN******************************************/
    function dispDelMsg()
    {
      global $wpdb, $user_ID;

      $delID = $_GET['id'];
      $toDuser = $wpdb->get_var($wpdb->prepare("SELECT to_user FROM {$this->fepTable} WHERE id = %d", $delID));
      $toDel = $wpdb->get_var($wpdb->prepare("SELECT to_del FROM {$this->fepTable} WHERE id = %d", $delID));
      $fromDel = $wpdb->get_var($wpdb->prepare("SELECT from_del FROM {$this->fepTable} WHERE id = %d", $delID));

      if ($toDuser == $user_ID)
      {
        if ($fromDel == 0)
          $wpdb->query($wpdb->prepare("UPDATE {$this->fepTable} SET to_del = 1 WHERE id = %d", $delID));
        else
          $wpdb->query($wpdb->prepare("DELETE FROM {$this->fepTable} WHERE id = %d OR parent_id = %d", $delID, $delID));
      }
      else
      {
        if ($toDel == 0)
          $wpdb->query($wpdb->prepare("UPDATE {$this->fepTable} SET from_del = 1 WHERE id = %d", $delID));
        else
          $wpdb->query($wpdb->prepare("DELETE FROM {$this->fepTable} WHERE id = %d OR parent_id = %d", $delID, $delID));
      }

      $this->success = __("Your message was successfully deleted!", "fep");

      return;
    }
	
	function dispDelMsg_admin()
    {
      global $wpdb, $user_ID;

      $delID = $_GET['id'];
	  
	  if (current_user_can('manage_options')) {
	  $wpdb->query($wpdb->prepare("DELETE FROM {$this->fepTable} WHERE id = %d OR parent_id = %d", $delID, $delID)); }

      $this->success = __("Message was successfully deleted!", "fep");

      return;
    }
/******************************************DELETE PAGE END******************************************/

/******************************************VIEW ANNOUNCEMENTS BEGIN******************************************/

    function dispAnnouncement()
    {
      global $wpdb, $user_ID;
      $announcements = $this->getAnnouncements();
      $num = $wpdb->num_rows;

      if ($this->deleteAnnouncement()) //Deleting an announcement?
      {
        $this->success = __("The announcement was successfully deleted!", "fep");
        return;
      }

      if (!$num) //Just viewing announcements
      {
        $announce = "<p><strong>".__("Announcements", "fep").":</strong></p>";
        if (current_user_can('manage_options'))
        {
          $announce .= $this->dispAnnounceForm();
        }
        $this->error = __("There are no announcements!", "fep");
      }
      else
      {
        $announce = "<p><strong>".__("Announcements", "fep").":</strong></p>";
        if (current_user_can('manage_options'))
        {
          $announce .= $this->dispAnnounceForm();
        }
        $announce .= "<table>";
        $a = 0;
        foreach ($announcements as $announcement)
        {
          $announce .= "<tr class='trodd".$a."'><td class='pmtext'><strong>".__("Subject", "fep").":</strong> ".$this->output_filter($announcement->message_title).
          "<br/><strong>".__("Date", "fep").":</strong> ".$this->formatDate($announcement->date);
          if (current_user_can('manage_options')) {
		  $announce .= "<br/><strong>".__("Added by", "fep").":</strong> ".get_userdata($announcement->from_user)->display_name;
            $announce .= "<br/><a href='".$this->actionURL."viewannouncements&del=1&id=".$announcement->id."' onclick='return confirm(\"".__('Are you sure?', 'fep')."\");'>".__("Delete", "fep")."</a>"; }
          $announce .= "<hr/>";
          $announce .= "<strong>".__("Message", "fep").":</strong><br/>".apply_filters("comment_text", $this->output_filter($announcement->message_contents))."</td></tr>";
          if ($a) $a = 0; else $a = 1; //Alternate table colors
        }
        $announce .= "</table>";
      }

      return $announce;
    }

    function dispAnnounceForm()
    {
		global $user_ID;
		$token = $this->getToken();

	$message_title = ( isset( $_REQUEST['message_title'] ) ) ? $_REQUEST['message_title']: '';
	$message_content = ( isset( $_REQUEST['message_content'] ) ) ? $_REQUEST['message_content']: '';
	
      $form = "<p>".__("Add a new announcement below", "fep")."</p>
      <form name='message' action='".$this->actionURL."addannouncement' method='post'>
      ".__("Subject", "fep").":<br/>
      <input type='text' name='message_title' value='".$message_title."' /><br/>".
      $this->get_form_buttons()."<br/>
      <textarea name='message_content'>".$message_content."</textarea>
	  <input type='hidden' name='message_from' value='".$user_ID."' />
	  <input type='hidden' name='token' value='".$token."' /><br/>
      <input type='submit' name='add-announcement' value='".__("Submit", "fep")."' />
      </form>";

      return $form;
    }

    function getAnnouncements()
    {
      global $wpdb; //message_read = 2 indicates that the msg is an announcement :)
      $results = $wpdb->get_results("SELECT * FROM {$this->fepTable} WHERE message_read = 2 ORDER BY id DESC");
      return $results;
    }

    function getAnnouncementsNum()
    {
      global $wpdb; //message_read = 2 indicates that the msg is an announcement :)
      $results = $wpdb->get_results("SELECT id FROM {$this->fepTable} WHERE message_read = 2 ORDER BY id DESC");
      return $wpdb->num_rows;
    }
	function getAnnouncementsNum_btn(){
	if ($this->getAnnouncementsNum()){
	  	$newmgs = " (<font color='red'>";
		$newmgs .= $this->getAnnouncementsNum();
		$newmgs .="</font>)";
		} else {
		$newmgs ="";}
		
		return $newmgs;
		}

    function addAnnouncement()
    {
      global $wpdb,$user_ID;
	  $adminOps = $this->getAdminOps();
      $title = $this->input_filter($_POST['message_title']);
      $contents = $this->input_filter($_POST['message_content']);
	  $from = $_POST['message_from'];
      $date = current_time('mysql');
      $read = '2';
	  
	  if (!$title || !$contents || $from != $user_ID)
      {
        if (!$title)
          $theError = __("You must enter a valid subject!", "fep");
        if (!$contents)
          $theError = __("You must enter some content!", "fep");
		 if ($from != $user_ID)
          $theError = __("Please try again!", "fep");
        $this->error = $theError;
        return $this->dispAnnounceForm();
		}
	  
	  // Check if a form has been sent
		$postedToken = filter_input(INPUT_POST, 'token');
		if (empty($postedToken))
      {
        $this->error = __("Invalid Token. Please try again!", "fep");
        return;
      }
  		if(!$this->isTokenValid($postedToken)){
    // Actually This is not first form submission. First Submission Pass this condition and inserted into db.
	$this->success = __("The announcement was successfully added!", "fep");
        return;
  			}
		//if nothing wrong continue
        $wpdb->query($wpdb->prepare("INSERT INTO {$this->fepTable} (from_user, message_title, message_contents, date, message_read) VALUES ( %s, %s, %s, %s, %d )",$from, $title, $contents, $date, $read));

	  if ($adminOps['notify_ann'] == 'on') {
	  $this->notify_users($title);
	  $this->success = __("The announcement was successfully added and sent email to all users!", "fep");
        return;
	  } else {
      $this->success = __("The announcement was successfully added!", "fep");
        return;
		}
    }

    function deleteAnnouncement()
    {
      global $wpdb;
	  if (isset($_GET['id'])){$delID = $_GET['id'];}
	  if (isset($_GET['del'])){$delm = $_GET['del'];}else{ $delm = ''; }
      if (current_user_can('manage_options') && $delm) //Make sure only admins can delete announcements
      {
        $wpdb->query($wpdb->prepare("DELETE FROM {$this->fepTable} WHERE id = %d", $delID));
        return true;
      }
      return false;
    }
	
	//Mass emails when announcement is created
		function notify_users($title) {
		
		$domain_name =  preg_replace('/^www\./','',$_SERVER['SERVER_NAME']);
		$usersarray = get_users("orderby=ID");
		$adminOps = $this->getAdminOps();
		$to = $adminOps['ann_to'];
		$from = 'noreply@'.$domain_name;
		
		$bcc = array();
		foreach  ($usersarray as $user) {
		$toOptions = $this->getUserOps($user->ID);
		$notify = $toOptions['allow_ann'];
		if (in_array($notify == 'true',$usersarray)){
		$bcc[] = $user->user_email;
		}
		}
		
	$chunked_bcc = array_chunk($bcc, 25);
	
	$subject = "" . get_bloginfo("name").": New Announcement";
	$message = "A new Announcement is Published in \r\n";
	$message .= get_bloginfo("name")."\r\n";
	$message .= "Title: ".$title. "\r\n";
	$message .= "Please Click the following link to view full Announcement. \r\n";
	$message .= $this->actionURL."viewannouncements \r\n";
	foreach($chunked_bcc as $bcc_chunk){
        $headers = array();
		$headers['From'] = 'From: '.get_bloginfo("name").'<'.$from.'>';
        $headers['Bcc'] = 'Bcc: '.implode(', ', $bcc_chunk);
        wp_mail($to , $subject, $message, $headers);
		}
		return;
    }
/******************************************VIEW ANNOUNCEMENTS END******************************************/

/******************************************MAIN DISPLAY BEGIN******************************************/
    function dispHeader()
    {
      global $user_ID, $user_login;

      $numNew = $this->getNewMsgs();
      $numAnn = $this->getAnnouncementsNum();
      $msgBoxSize = $this->getUserNumMsgs();
      $adminOps = $this->getAdminOps();
      if ($adminOps['num_messages'] == 0 || current_user_can('manage_options'))
        $msgBoxTotal = __("Unlimited", "fep");
      else
        $msgBoxTotal = $adminOps['num_messages'];

      $header = "<div id='fep-wrapper'>";
      $header .= "<div id='fep-header'>";
      $header .= get_avatar($user_ID, 55)."<p><strong>".__("Welcome", "fep").": ".$this->convertToDisplay($user_login)."</strong><br/>";
      $header .= __("You have", "fep")." (<font color='red'>".$numNew."</font>) ".__("new messages", "fep").
      " ".__("and", "fep")." (<font color='red'>".$numAnn."</font>) ".__("announcement(s)", "fep")."<br/>";
      if ($msgBoxTotal == __("Unlimited", "fep") || $msgBoxSize < $msgBoxTotal)
        $header .= __("Message box size", "fep").": ".$msgBoxSize." ".__("of", "fep")." ".$msgBoxTotal."</p>";
      else
        $header .= "<font color='red'>".__("Your Message Box Is Full! Please delete some messages.", "fep")."</font></p>";
      $header .= "</div>";
      return $header;
    }

    function dispMenu()
    {

      $numNew = $this->getNewMsgs_btn();
	  $numNewadm = $this->getNewMsgs_admin();
	  $numAnn = $this->getAnnouncementsNum_btn();
	  
      $menu = "<div id='fep-menu'>";
      $menu .= "<a class='fep-button' href='".$this->pageURL."'>".__("Message Box".$numNew."", "fep")."</a>";
      $menu .= "<a class='fep-button' href='".$this->actionURL."viewannouncements'>".__("Announcements".$numAnn."", "fep")."</a>";
      $menu .= "<a class='fep-button' href='".$this->actionURL."newmessage'>".__("New Message", "fep")."</a>";
	  if($this->adminOps['hide_directory'] != 'on' || current_user_can('manage_options'))
      $menu .= "<a class='fep-button' href='".$this->actionURL."directory'>".__("Directory", "fep")."</a>";
      $menu .= "<a class='fep-button' href='".$this->actionURL."settings'>".__("Settings", "fep")."</a>";
	  if(current_user_can('manage_options'))
		$menu .= "<a class='fep-button' href='".$this->actionURL."viewallmgs'>".__("Other's Message".$numNewadm."", "fep") . "</a>";
		$menu .="</div>";
      $menu .= "<div id='fep-content'>";
      return $menu;
    }

    function dispNotify()
    {
	if ($this->success != ""){
      $notify = "<div id='success'>".$this->success."</div>";
	  } else if ($this->error != "") {
	  $notify = "<div id='error'>".$this->error."</div>";
	  }
      return $notify;
    }

    function dispFooter()
    {
      $footer = "</div>"; //End content
        //Maybe Add Notify
        if ($this->error != "" || $this->success != "")
          $footer .= $this->dispNotify();
      
      if($this->adminOps['hide_branding'] != 'on')
        $footer .= "<div id='fep-footer'><a href='http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/'>Front End PM ".$this->get_version()."</a></div>";
      
      $footer .= "</div>"; //End main wrapper
      
      return $footer;
    }

    function dispDirectory()
    {
	if($this->adminOps['hide_directory'] == 'on' && !current_user_can('manage_options'))
	  return;
      $users = $this->get_users();
	  $result = count_users();
	  $total = $result['total_users'];
	  $adminOps = $this->getAdminOps();
      if ($total)
      {
        $directory = "<p><strong>".__("Total Users", "fep").": (".$total.")</strong></p>";
        $numPgs = $total / $adminOps['user_page'];
        if ($numPgs > 1)
        {
          $directory .= "<p><strong>".__("Page", "fep").": </strong> ";
          for ($i = 0; $i < $numPgs; $i++)
            if ($_GET['upage'] != $i)
              $directory .= "<a href='".$this->actionURL."directory&upage=".$i."'>".($i+1)."</a> ";
            else
              $directory .= "[<b>".($i+1)."</b>] ";
          $directory .= "</p>";
        }
		$directory .= "<table><tr class='head'>
        <th width='50%'>".__("User", "fep")."</th>
        <th width='50%'>".__("Send Message", "fep")."</th></tr>";
		$a=0;

      foreach($users as $u)
      {
	  $directory .= "<tr class='trodd".$a."'><td>".$u->display_name."</td>";
          $directory .= "<td><a href='".$this->actionURL."newmessage&to=".$u->user_login."'>".__("Send Message", "fep")."</a></td></tr>";
		  if ($a) $a = 0; else $a = 1;
      }
	  $directory .= "</table>";

        return $directory;
      }
      else
      {
        $this->error = __("No User!", "fep");
        return;
      }
    }

    //Display the proper contents
   function displayAll()
    {
      global $user_ID,$wpdb;
      if ($user_ID)
      {
        //Finish the setup since these wouldn't work in the constructor
        $this->userOps = $this->getUserOps($user_ID);
        $this->setPageURLs();

        //Add header
        $out = $this->dispHeader();

        //Add Menu
        $out .= $this->dispMenu();

        //Start the guts of the display
		if (isset($_GET['fepaction'])){
		$switch = $_GET['fepaction'];
		}else{ $switch = '';}
        switch ($switch)
        {
          case 'newmessage':
            $out .= $this->dispNewMsg();
            break;
          case 'checkmessage':
            $out .= $this->dispCheckMsg();
            break;
          case 'viewmessage':
            $out .= $this->dispReadMsg();
            break;
		case 'viewmessageadmin':
		if (current_user_can('manage_options'))
            $out .= $this->dispReadMsg_admin();
			else
			$out .= $this->dispReadMsg();
            break;
          case 'deletemessage':
            $out .= $this->dispDelMsg();
            break;
		case 'deletemessageadmin':
		if (current_user_can('manage_options'))
            $out .= $this->dispDelMsg_admin();
			else
			$out .= $this->dispDelMsg();
            break;
          case 'directory':
		  if($this->adminOps['hide_directory'] != 'on' || current_user_can('manage_options'))
            $out .= $this->dispDirectory();
			else
			$out .= $this->dispMsgBox();
            break;
          case 'settings':
            $out .= $this->dispUserPage();
            break;
          case 'viewannouncements':
            $out .= $this->dispAnnouncement();
            break;
		  case 'addannouncement':
            $out .= $this->addAnnouncement();
            break;
          case 'viewallmgs':
          if (current_user_can('manage_options'))
            $out .= $this->dispMsgBox_admin();
			else
			$out .= $this->dispMsgBox();
            break;
          default: //Message box is shown by Default
            $out .= $this->dispMsgBox();
            break;
        }

        //Add footer
        $out .= $this->dispFooter();
      }
      else
      {
        $out = "<p><strong>".__("You must be logged-in to view your message.", "fep")."</strong></p>";
      }
      return $out;
    }
/******************************************MAIN DISPLAY END******************************************/

/******************************************MISC. FUNCTIONS BEGIN******************************************/

 /**
 * Creates a token usable in a form
 * @return string
 */
 	function session(){
 	if(!isset($_SESSION)) {
            session_start();
        } 
		}
 
	function getToken(){
  		$token = sha1(mt_rand());
  		if(!isset($_SESSION['tokens'])){
   	 $_SESSION['tokens'] = array($token => 1);
  	}else{
    $_SESSION['tokens'][$token] = 1;
  }
  return $token;
}	

 /**
 * Check if a token is valid. Removes it from the valid tokens list
 * @param string $token The token
 * @return bool
 */
	function isTokenValid($token){
 	 if(!empty($_SESSION['tokens'][$token])){
    unset($_SESSION['tokens'][$token]);
    	return true;
  		}
 	 return false;
	}
	
	//Check is user blocked by admin
	function have_permission(){
	global $current_user;
	$adminOps = $this->getAdminOps();
	$wpusers = (array) explode(',', $adminOps['have_permission']);
	$valid_wpusers = array();
	foreach($wpusers as $wpuser){
		$wpuser = trim($wpuser);
		if($wpuser!=''){
			$user = get_user_by('login', $wpuser);
			if($user){
				$valid_wpusers[] = $user->ID;
			}
			$valid_wpusers = array_unique($valid_wpusers);
			if(in_array($current_user->ID, $valid_wpusers)){
			return false;
			}
			} }
	return true;
	}
	
    function get_users()
    {
      global $wpdb;
	  if (isset($_GET['upage'])){
	  $page = $_GET['upage'];
	  }else{$page = 0;}
      $adminOps = $this->getAdminOps();
      $start = $page * $adminOps['user_page'];
      $end = $adminOps['user_page'];
      $users = $wpdb->get_results($wpdb->prepare("SELECT display_name, user_login, ID FROM $wpdb->users ORDER BY display_name ASC LIMIT %d, %d",$start,$end));
	  return $users;	
    }

    function get_form_buttons()
    {
      $button = '
      <a title="'.__("Bold", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[b]", "[/b]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/b.png" /></a>
      <a title="'.__("Italic", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[i]", "[/i]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/i.png" /></a>
      <a title="'.__("Underline", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[u]", "[/u]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/u.png" /></a>
      <a title="'.__("Strikethrough", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[s]", "[/s]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/s.png" /></a>
      <a title="'.__("Code", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[code]", "[/code]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/code.png" /></a>
      <a title="'.__("Quote", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[quote]", "[/quote]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/quote.png" /></a>
      <a title="'.__("List", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[list]", "[/list]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/list.png" /></a>
      <a title="'.__("List item", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[*]", "", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/li.png" /></a>
      <a title="'.__("Link", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[url]", "[/url]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/url.png" /></a>
      <a title="'.__("Image", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[img]", "[/img]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/img.png" /></a>
      <a title="'.__("Email", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[email]", "[/email]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/email.png" /></a>
      <a title="'.__("Add Hex Color", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[color=#]", "[/color]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/color.png" /></a>
            <a title="'.__("Embed", "fep").'" href="javascript:void(0);" onclick=\'surroundTheText("[embed]", "[/embed]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/embed.png" /></a>';

      return $button;
    }

    function output_filter($string)
    {
      $parser = new fepBBCParser();
	  $html = stripslashes($parser->bbc2html($string));
      return ent2ncr($html);
    }

    function input_filter($string)
    {
      $newStr = esc_attr($string);
      return wp_strip_all_tags($newStr);
    }

    function getUserNumMsgs()
    {
      global $wpdb, $user_ID;
      $get_messages = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE (to_user = %d AND parent_id = 0 AND to_del <> 1) OR (from_user = %d AND parent_id = 0 AND from_del <> 1)", $user_ID, $user_ID));
      $num = $wpdb->num_rows;
      return $num;
    }

    function formatDate($date)
    {
		$now = current_time('mysql');
      //return date('M d, h:i a', strtotime($date));
	  return human_time_diff(strtotime($date),strtotime($now)).' ago';
    }
	
	function TimeDelay($DeTime)
    {
		global $wpdb, $user_ID;
		$now = current_time('mysql');
		$Dtime = $DeTime * 60;
		$Prev = $wpdb->get_var($wpdb->prepare("SELECT last_date FROM {$this->fepTable} WHERE parent_id = 0 AND last_sender = %d ORDER BY last_date DESC LIMIT 1", $user_ID));
	  $diff = strtotime($now) - strtotime($Prev);
	  $diffr = $diff/60;
	  $next = strtotime($Prev) + $Dtime;
	  $Ntime = human_time_diff(strtotime($now),$next);
	   return array('diffr' => $diffr, 'time' => $Ntime);
    }
	
	function is_positive($str) {
 	 return (is_numeric($str) && $str > 0 && $str == round($str));
	}

    function getNewMsgs()
    {
      global $wpdb, $user_ID;

      $get_pms = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE (to_user = %d AND parent_id = 0 AND to_del <> 1 AND message_read = 0 AND last_sender <> %d) OR (from_user = %d AND parent_id = 0 AND from_del <> 1 AND message_read = 0 AND last_sender <> %d)", $user_ID, $user_ID, $user_ID, $user_ID));
      return $wpdb->num_rows;
    }
	function getNewMsgs_btn(){
	if ($this->getNewMsgs()){
	  	$newmgs = " (<font color='red'>";
		$newmgs .= $this->getNewMsgs();
		$newmgs .="</font>)";
		} else {
		$newmgs = "";}
		
		return $newmgs;
		}
	
	
	function getNewMsgs_admin()
    {
      global $wpdb, $user_ID;

      $get_pmss = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE to_user <> %d AND from_user <> %d AND last_sender <> %d AND message_read = 0 AND parent_id = 0", $user_ID, $user_ID, $user_ID));
	  if ($wpdb->num_rows){
	  	$newmgs = " (<font color='red'>";
		$newmgs .= $wpdb->num_rows;
		$newmgs .="</font>)";
		} else {
		$newmgs ="";}
		
		return $newmgs;
    }

    function autoembed($string)
    {
      global $wp_embed;
      if (is_object($wp_embed))
        return $wp_embed->autoembed($string);
      else
        return $string;
    }

    function get_version()
    {
      $plugin_data = implode('', file($this->pluginDir."front-end-pm.php"));
      if (preg_match("|Version:(.*)|i", $plugin_data, $version))
        $version = $version[1];
      return $version;
    }
/******************************************MISC. FUNCTIONS END******************************************/
  } //END CLASS
} //ENDIF
?>