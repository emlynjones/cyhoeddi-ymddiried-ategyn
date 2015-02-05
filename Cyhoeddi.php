<?php
/**
 * Plugin Name: Cyhoeddi
 * Plugin URI: http://www.cambrianweb.com
 * Description: Galluogi i unigolion gyhoeddi pyst o'r tu blaen i wefan
 * Version: 1.0.0
 * Author: Gwe Cambrian Web
 * Author URI: http://www.cambrianweb.com
 * Text Domain: cyhoeddi-ategyn-wp
 * Domain Path: /
 * License: GPL2
 */


//Ar gychwyn yr ategyn, gwneud y canlynol:
register_activation_hook(__FILE__,'cychwyn_cyhoeddi');

function cychwyn_cyhoeddi (){
	//Creu y rol y bydd y defnyddiwr
	// Methu postio'n fyw
	//gallu llwytho lluniau (.jpg/.png yn unig?)
	//
	//
	//
	$capabilities = array(
		'upload_files' => true,
		'edit_published_pages' => true,
		'edit_pages' => true,
		'edit_others_pages' => true
		);
	add_role( 'rol_adroddwr_ymddiried', 'Rol Ymddiried', $capabilities );

	
}


add_action('init', 'cyhoeddi_init');

function cyhoeddi_init() {

//Cofrestru math post "Adroddiad" i storio'r data.
	$labels = array(
		'name' => __('Adroddiadau', 'cyhoeddi-ategyn-wp'),
		'singular_name' => __('Adroddiad', 'cyhoeddi-ategyn-wp'),
		'add_new' => __('Adio Newydd', 'cyhoeddi-ategyn-wp'),
		'add_new_item' => __('Adio Adroddiad Newydd', 'cyhoeddi-ategyn-wp'),
		'edit_item' => __('Newid Adroddiad', 'cyhoeddi-ategyn-wp'),
		'new_item' => __('Adroddiad Newydd', 'cyhoeddi-ategyn-wp'),
		'all_items' => __('Holl Adroddiadau', 'cyhoeddi-ategyn-wp'),
		'view_item' => __('Gweld Adroddiadau', 'cyhoeddi-ategyn-wp'),
		'search_items' => __('Chwilio Adroddiadau', 'cyhoeddi-ategyn-wp'),
		'not_found' => __('Dim adroddiadau wedi eu canfod...', 'cyhoeddi-ategyn-wp'),
		'not_found_in_trash' => __('Dim adroddiadau wedi eu canfod yn y bin...', 'cyhoeddi-ategyn-wp'),
		'menu_name' => __('Adroddiadau', 'cyhoeddi-ategyn-wp')
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'has_archive' => true,
		'hierarchical' => true,
		'menu_positions' => true,
		'supports' => array ('title','editor','thumbnail')
	);
	$math_post = 'adroddiadau';

	register_post_type($math_post, $args);
	
	// Creu y defnyddiwr fydd yn cael ei logio mewn.
	$data_defnyddiwr = array(
		'user_login' => 'adroddwr_ymddiried',
		'user_pass' => 'cyfrinair',
		'user_email' => 'info@cambrianweb.com',
		'role' => 'rol_adroddwr_ymddiried'
		);

	wp_insert_user( $data_defnyddiwr);

//Darn yma yn trin y data a gaiff ei fewnbynnu o ochr weinyddol y safle ( a galluogi golygu os oes angen)

	// Galw/cofrestru ardal newydd ar gyfer dangos/golygu y data
	add_action('add_meta_boxes', 'add_meta_boxes_adroddiadau',10,2);

	function add_meta_boxes_adroddiadau($math_post, $post){
			add_meta_box('cyhoeddi-meta', __('Gwybodaeth yr Adroddiad', 'cyhoeddi-ategyn-wp'),
		'cyhoeddi_cyhoeddi_metabocsys',
		'adroddiadau',
		'normal',
		'high');
	}

	//global $post;
	

	//Cyhoeddi y ffurflen ddata sydd yn dangos y data ychwanegol.
	function cyhoeddi_cyhoeddi_metabocsys(){

		wp_nonce_field('cyhoeddi_safio_metafocsys', 'cyhoeddi_meta_nonce');

		global $post;

		$awdur_adroddiad = get_post_meta($post->ID, '_cyh_awdur', true);
		echo'<p><label for="awdur">'.__('Awdur','cyhoeddi-ategyn-wp').'</label><br />';

		echo'<input type="text" id="awdur" value="'.$awdur_adroddiad.'" tabindex="1" name="awdur" /></p>';
		
	}
	

	add_action('save_post', 'cyhoeddi_safio_metafocsys');

	//Galluogi safio y gwaith
	function cyhoeddi_safio_metafocsys($post_id){
		if (get_post_type($post_id) == 'adroddiadau'){

			//Peidio safio y gwaith os yw'r system yn safio'n awtomatig ar hyn o bryd
			if(defined('DOING_AUTOSAVE') && 'DOING_AUTOSAVE')
			return;

			//Cadarnhau fod y nonce yno, a bod y meysydd priodol wedi'u llenwi
			if(isset($_POST['cyhoeddi_meta_nonce']) && wp_verify_nonce($_POST['cyhoeddi_meta_nonce'], 'cyhoeddi_safio_metafocsys') && check_admin_referer('cyhoeddi_safio_metafocsys', 'cyhoeddi_meta_nonce')){
				//Dim ond angen safio os yw'r blwch wedi ei lenwi
				if(isset($_POST['awdur'])){
					$awdur_adroddiad_newydd = esc_attr($_POST['awdur']);

					update_post_meta($post_id, '_cyh_awdur', strip_tags($awdur_adroddiad_newydd));
				}


			};
		};
	};

	// Gwneud yn sir fod y ffurflen wedi ei bostio (botwm wedi ei glicio), a bod y nonce wedi ei lenwi
		if(!empty($_POST['nonce-cyhoeddi-drafft'])) {
			
			//Logio mewn i warantu yr un defnyddiwr sydd yn gwirio'r nonce
			do_action('logio_mewn_awtomatig');

			// Cadarnhau bod y mesydd sydd yn ofynnol yn bresennol
			if (!empty($_POST['teitl'])) {
				$teitl =  $_POST['teitl'];
			}
			elseif(strlen($_POST['teitl']) > 80){
				$args = array(
					'response' => 500,
					'back_link' => true,
					);
				wp_die(__("Mae'n rhaid i'r 'Teitl' fod yn llai na 80 nod.", 'cyhoeddi-ategyn-wp'),'',$args);
			}
			
			else {
				$args = array(
					'response' => 500,
					'back_link' => true,
					);
				wp_die(__("Mae'r 'Teitl' yn ofynnol.", 'cyhoeddi-ategyn-wp'),'',$args);
			}

			if (!empty($_POST['testunadroddiad'])) {
				$testunadroddiad = $_POST['testunadroddiad'];
			}
			else {
				$args = array(
					'response' => 500,
					'back_link' => true,
					);
				wp_die(__('Mae testun yr adroddiad yn ofynnol','cyhoeddi-ategyn-wp'),'',$args);
			}
			if(!empty($_POST['awdur'])){
				$awdur = $_POST['awdur'];
			}
			// Saniteiddio a cadarnhau nad ydy'r mewnbynnau yn ddrwg yma
			//
			//
			//


			// Angen safio y data ansafonol
			//
			//
			//





			// Adio cynnwys y ffurflen fel post i'r system fel "stori/straeon"

			$teitl_glan = sanitize_text_field($teitl);
			$testunadroddiad_glan = wp_kses_post($testunadroddiad);
			$awdur_glan = sanitize_text_field($awdur);

			$post = array(
				'post_title'	=> $teitl_glan,
				'post_content'	=> $testunadroddiad_glan,  
				'post_status'	=> 'pending',			// Angen iddo fod yn ddraft nes i'r gweinyddwyr edrych a chadarnhau iddo fod yn ddilys
				'post_type'	=> $math_post  // Postio i'r math post yr ydym wedi ei greu
			);
			
			//Gwneud yn siwr fod y nonce yn ddilys, ac os ydio, safio y gwybodaeth yn y BD
			$cyhoeddi_nonce_ffurflen = $_POST['nonce-cyhoeddi-drafft'];

			if(wp_verify_nonce($cyhoeddi_nonce_ffurflen, 'nonce_cyhoeddi_byrgod')) {
				$post_id = wp_insert_post($post);  // Pasio $post i'r system WP er mwyn ei gyhoeddi fel "pending"
										// http://codex.wordpress.org/Function_Reference/wp_insert_post
				update_post_meta($post_id, '_cyh_awdur', strip_tags($awdur_glan));

				do_action('wp_insert_post', 'wp_insert_post');
				
				// Gorfodi logio allan i wneud yn siwr nad yw'r unigolion sydd wedi logio mewn yn gallu newid data eraill.
				wp_logout();

				//gyrru ebost i'r gweinyddwr i adael gwybod bod mewnbwn wedi ei wneud.
				$ebostio = get_option('admin_email');
				$pwnc = __('Adroddiad wedi ei gynnig gan ', 'cyhoeddi-ategyn-wp').$awdur_glan;
				$neges = __('Annwyl Weinyddwr,<br/>Mae adroddiad newydd wedi ei gynnig o\'r wefan.','cyhoeddi-ategyn-wp');
				$pennawdau[] = 'Content-Type: text/html; charset=UTF-8';
				$pennawdau[] = 'From: Gwefan Ymddiried <emlyn@cambrianweb.com>';
				$pennawdau[] = 'Cc: Gwe Cambrian Web <emlynjones121@gmail.com>';
				
				
				wp_mail($ebostio, $pwnc, $neges, $pennawdau);
				
						
				function gosod_y_testun_yn_html() {
					return 'text/html';
					}

				//Ailgyfeirio y defnyddiwr i dudalen arall.
				wp_redirect(home_url());

				exit;
			}
			else{
				echo 'Wedi methu gwirior nonce...';
				echo $cyhoeddi_nonce_ffurflen;
			}
		}
	
}

	// Creu y byrgod fydd yn galw'r ffurflen ar y tu blaen.
add_shortcode( 'cyhoeddi-ffurflen', 'cyhoeddi_galwffurflen' );

// Angen gorfodi defnyddiwr i logio mewn yn brogramyddol
function cyhoeddi_logio_defnyddiwr_mewn(){
	//checio os yw'r defnyddiwr wedi logio mewn yn barod er mwyn osgoi logio allan yr admin
	if(!is_user_logged_in()){
		
		$enw_defnyddiwr = 'adroddwr_ymddiried';

		add_filter( 'authenticate', 'cyhoeddi_allow_programmatic_login', 10, 3 );    // hook in earlier than other callbacks to short-circuit them
			$defnyddiwr = wp_signon( array( 'user_login' => $enw_defnyddiwr ) );
		remove_filter( 'authenticate', 'cyhoeddi_allow_programmatic_login', 10, 3 );
		
		
	//Gosod y defnyddiwr ar gyfer y byrgod yn unig - hy dim ar dudalennau eraill.
		if ( is_a( $defnyddiwr, 'WP_User')){
			wp_set_current_user( $defnyddiwr->ID, $defnyddiwr->user_login );
			if ( is_user_logged_in() ) {
				return true;
				}
			return false;
		}
	}
}

function cyhoeddi_allow_programmatic_login( $defnyddiwr, $enw_defnyddiwr, $cyfrinair ) {
	return get_user_by( 'login', $enw_defnyddiwr);
};

//Galluogi galw'r logio mewn o unrhywle
add_action('logio_mewn_awtomatig', 'cyhoeddi_logio_defnyddiwr_mewn');

function cyhoeddi_galwffurflen( $atts ){
		/*
			Enwau meysydd:
			teitl
			awdur
			gwefan-awdur
			enw-prosiect
			gwefan-prosiect
			testunadroddiad

		*/

		//Tanio y logio mewn yn awtomatig
		do_action('logio_mewn_awtomatig');

		// Angen cyn-lenwi rhain hefo beth sydd yn _Post - rhag ofn i'r ffurflen fethu & bod y defnyddiwr yn colli y data.
		//
		//
		//
		//


		//HTML y ffurflen
		ob_start();
		echo '<h2>'.__("Llenwch y gwybodaeth isod os gwelwch yn dda.","cyhoeddi-ategyn-wp").'</h2>';

		echo '<div id="div-ffurflen">';

			echo'<form id="cyhoeddi-ffurflen-stori" name="cyhoeddi-ffurflen-stori" method="post" action="">';
				//Teitl llawn y post
				echo'<p><label for="teitl">'.__('Teitl','cyhoeddi-ategyn-wp').'</label><br />';

					echo'<input type="text" id="teitl" value="" tabindex="1" size="100%" name="teitl" /></p>';
					//Gofyn am enw yr awdur - dim angen creu proffil iddynt
				echo'<p><label for="awdur">'.__('Awdur','cyhoeddi-ategyn-wp').'</label><br />';

					echo'<input type="text" id="awdur" value="" tabindex="1" size="100%" name="awdur" /></p>';
				echo '<br/><br/>';
				/*
					//Linc i wefan yr awdur (opsiynol)
				echo'<p><label for="gwefan-awdur">'.__('Gwefan yr Awdur','cyhoeddi-ategyn-wp').'</label><br />';

					echo'<input type="text" id="gwefan-awdur" value="" tabindex="1" size="100%" name="gwefan-awdur" /></p>';
				
					//Enw prosiect ??(Opsiynol)
				echo'<p><label for="enw-prosiect">'.__('Enw y Prosiect','cyhoeddi-ategyn-wp').'</label><br />';

					echo'<input type="text" id="enw-prosiect" value="" tabindex="1" size="100%" name="enw-prosiect" /></p>';
					
					//Linc i'r brosiect ?? (Opsiynol)
				echo'<p><label for="gwefan-prosiect">'.__('Gwefan y Brosiect','cyhoeddi-ategyn-wp').'</label><br />';

					echo'<input type="text" id="gwefan-prosiect" value="" tabindex="1" size="100%" name="gwefan-prosiect" /></p>';	
				*/
					if(!empty($_POST['testunadroddiad'])){
						$cynnwys = $_POST['testunadroddiad'];
					}
					else{$cynnwys = '';};

					$id_golygydd = 'idtestunadroddiad';
					$gosodiadau = array(
					'wpautop' => true,
					'media_buttons'=>true,
					'textarea_name'=>'testunadroddiad'
					);

					echo wp_editor( $cynnwys, $id_golygydd,$gosodiadau );

					$html_nonce = wp_nonce_field('nonce_cyhoeddi_byrgod', 'nonce-cyhoeddi-drafft');
					echo "<p>".$html_nonce."</p>";

				echo'<p><input type="submit" value="'.__('Iawn', 'cyhoeddi-ategyn-wp').'" tabindex="6" id="submit" name="submit" /></p>';

			echo'</form></div>';
		$html_ffurflen = ob_get_clean();
		
		ob_end_clean();

		return $html_ffurflen;
		
		}

// Creu panel yn yr ochr weinyddu sydd yn dangos linc i'r straeon sydd heb eu cyhoedd (sortio wrth ddyddiad) & opsiwn i gyhoeddi yn y fan a'r lle
//
//
//

// Creu byrgod sydd yn allbynnu grid/rhestr o adroddiadau (sortio wrth ddyddiad)

add_shortcode( 'cyhoeddi-adroddiadau', 'cyhoeddi_adroddiadau' );

function cyhoeddi_adroddiadau(){
	$loopdiloop = new WP_Query( array( 'post_type' => 'adroddiadau', 'posts_per_page' => -1 ) );
		global $post;

		while ( $loopdiloop->have_posts() ) : $loopdiloop->the_post();
			$adroddiad_awdur = get_post_meta($post->ID, '_cyh_awdur', true);
			$cynnwys_di_html = wp_strip_all_tags(the_content());
			$cynnwys_torri_lawr = mb_strimwidth($cynnwys_di_html, 0, 250, '...');


			echo '<h2>'.the_title();
			echo'</h2>';
			echo '<em>gan '.$adroddiad_awdur.'</em>';
			echo $cynnwys_torri_lawr;
			echo 'Dyma Dysan!';

		endwhile; wp_reset_query();

}


?>