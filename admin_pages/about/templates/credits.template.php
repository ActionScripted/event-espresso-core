<p class="about-description"><?php _e('Event Espresso is created by a worldwide team of passionate people with a drive to empower your events!', 'event_espresso'); ?></p>
<h4 class="wp-people-group"><?php _e('Owners', 'event_espresso'); ?></h4>
<ul class="wp-people-group" id="ee-people-group-owners">
	<li class="wp-person" id="ee-person-sshoultes">
		<a href="<?php esp_gravatar_profile('seth@eventespresso.com'); ?>">
			<?php echo esp_gravatar_image( 'seth@eventespresso.com', 'Seth Shoultes' ); ?>
		</a>
		<a class="web" href="<?php esp_gravatar_profile('seth@eventespresso.com'); ?>">
			Seth Shoultes
		</a>
		<span class="title"><?php _e('Co-Founder', 'event_espresso'); ?></span>
	</li>
	<li class="wp-person" id="ee-person-gkoyle">
		<a href="<?php esp_gravatar_profile('garth@eventespresso.com'); ?>">
			<?php echo esp_gravatar_image( 'garth@eventespresso.com', 'Garth Koyle' ); ?>
		</a>
		<a class="web" href="<?php esp_gravatar_profile('garth@eventespresso.com'); ?>">
			Garth Koyle
		</a>
		<span class="title"><?php _e('Co-Founder', 'event_espresso'); ?></span>
	</li>
</ul>
<h4 class="wp-people-group"><?php _e('Core Developers', 'evnet_espresso'); ?></h4>
<ul class="wp-people-group" id="ee-people-group-core-developers">
	<li class="wp-person" id="ee-person-bchristensen">
		<a href="<?php esp_gravatar_profile('brent@eventespresso.com'); ?>">
			<?php echo esp_gravatar_image( 'brent@eventespresso.com', 'Brent Christensen' ); ?>
		</a>
		<a class="web" href="<?php esp_gravatar_profile('brent@eventespresso.com'); ?>">
			Brent Christensen
		</a>
		<span class="title"><?php _e('Lead Developer', 'event_espresso'); ?></span>
	</li>
	<li class="wp-person" id="ee-person-dethier">
		<a href="<?php esp_gravatar_profile('darren@roughsmootheng.in'); ?>">
			<?php echo esp_gravatar_image( 'darren@roughsmootheng.in', 'Darren Ethier' ); ?>
		</a>
		<a class="web" href="<?php esp_gravatar_profile('darren@roughsmootheng.in'); ?>">
			Darren Ethier
		</a>
		<span class="title"><?php _e('Core Developer', 'event_espresso'); ?></span>
	</li>
	<li class="wp-person" id="ee-person-mnelson">
		<a href="<?php esp_gravatar_profile('michael@eventespresso.com'); ?>">
			<?php echo esp_gravatar_image( 'michael@eventespresso.com', 'Michael Nelson' ); ?>
		</a>
		<a class="web" href="<?php esp_gravatar_profile('michael@eventespresso.com'); ?>">
			Michael Nelson
		</a>
		<span class="title"><?php _e('Core Developer', 'event_espresso'); ?></span>
	</li>
</ul>
<h4 class="wp-people-group"><?php _e('Support Staff', 'evnet_espresso'); ?></h4>
<ul class="wp-people-group" id="ee-people-group-support-staff">
	<li class="wp-person" id="ee-person-jfeck">
		<a href="<?php esp_gravatar_profile('josh@eventespresso.com'); ?>">
			<?php echo esp_gravatar_image( 'josh@eventespresso.com', 'Josh Feck' ); ?>
		</a>
		<a class="web" href="<?php esp_gravatar_profile('josh@eventespresso.com'); ?>">
			Josh Feck
		</a>
	</li>
	<li class="wp-person" id="ee-person-drobinson">
		<a href="<?php esp_gravatar_profile('dean@eventespresso.com'); ?>">
			<?php echo esp_gravatar_image( 'dean@eventespresso.com', 'Dean Robinson' ); ?>
		</a>
		<a class="web" href="<?php esp_gravatar_profile('dean@eventespresso.com'); ?>">
			Dean Robinson
		</a>
	</li>
	<li class="wp-person" id="ee-person-jwilson">
		<a href="<?php esp_gravatar_profile('jonathon@eventespresso.com'); ?>">
			<?php echo esp_gravatar_image( 'jonathon@eventespresso.com', 'Jonathon Wilson' ); ?>
		</a>
		<a class="web" href="<?php esp_gravatar_profile('jonathon@eventespresso.com'); ?>">
			Jonathon Wilson
		</a>
	</li>
	<li class="wp-person" id="ee-person-sharrel">
		<a href="<?php esp_gravatar_profile('sidney@eventespresso.com'); ?>">
			<?php echo esp_gravatar_image( 'sidney@eventespresso.com', 'Sidney Harrel' ); ?>
		</a>
		<a class="web" href="<?php esp_gravatar_profile('sidney@eventespresso.com'); ?>">
			Sidney Harrel
		</a>
	</li>
</ul>
<h4 class="wp-people-group"><?php _e('External Libraries', 'event_espresso'); ?></h4>
<p class="description">
	<?php printf( __('Along with the libraries %sincluded with WordPress%s, Event Espresso utilizes the following third party libraries:', 'event_espresso'), '<a href="credits.php">', '</a>' ); ?>
</p>
<p class="wp-credits-list">
	<a href="http://josscrowcroft.github.io/accounting.js/"><?php _e('accounting.js', 'event_espresso'); ?></a>,
	<a href="http://dompdf.github.io/"><?php _e('dompdf', 'event_espresso'); ?></a>,
	<a href="http://zurb.com/playground/jquery-joyride-feature-tour-plugin"><?php _e('joyride2', 'event_espresso'); ?></a>,
	<a href="http://raveren.github.io/kint/"><?php _e('Kint', 'event_espresso'); ?></a>,
	<a href="http://momentjs.com/"><?php _e('Moment.js', 'event_espresso'); ?></a>,
	<a href="http://qtip2.com/"><?php _e('qTip2', 'event_espresso'); ?></a>,
	<a href="http://trentrichardson.com/examples/timepicker/"><?php _e('jQuery UI Timepicker', 'event_espresso'); ?></a>,
	<a href="https://github.com/jhogendorn/jQuery-serializeFullArray"><?php _e('SerializeFullArray', 'event_espresso'); ?></a>
	<a href="https://github.com/jzaefferer/jquery-validation"><?php _e('jQuery Validation', 'event_espresso'); ?></a>
</p>

<?php
	function esp_gravatar_profile($email) {
		echo 'http://www.gravatar.com/' . md5($email);
	}

	function esp_gravatar_image($email, $name) {
		echo '<img src="http://0.gravatar.com/avatar/' . md5($email) . '?s=60" class="gravatar" alt="' . $name . '"/>';
	}
?>