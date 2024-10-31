<?php defined( 'ABSPATH' ) || exit; ?>
<div id="nopea-media-settings-page" class="wrap">

	<h1><?php esc_html_e( 'Nopea.media settings', 'nopea-media' ); ?></h1>
	<form method="post">
		<table class="form-table">
			<tbody>

			 <?php if($quota = get_option(sprintf('%s_publication_quota', NOPME_PREFIX))): $quota = explode(',', $quota) ?>
			  <tr>
			   <th scope="row"><?php esc_html_e( 'Info', 'nopea-media' ); ?></th>
				<td>  

				 <fieldset>
					<h4> <?= esc_html_e('PDF publication quota: ',  'nopea-media') ?> <?=$quota[0] .'/'. $quota[1]?> </h4> 
					<h4> <?= esc_html_e('Subscription and quota renews on: ',  'nopea-media') ?><?=$quota[2]?> </h4> 
				</fieldset>

				</td>
       		  </tr>

			 <?php endif;?>	

				<tr>
					<th scope="row"><?php esc_html_e( 'PDF Color Scheme', 'nopea-media' ); ?></th>
					<td>
						<fieldset>
							<label>
                                <input type="text" name="primary_color_cmyk" value="<?=get_option('primary_color_cmyk') ?? ''?>" 
									placeholder="100,0,0,59" class="regular-text" required pattern="^\d{1,3},\d{1,3},\d{1,3},\d{1,3}$">
								<span class="description"><?php esc_html_e( 'Primary color (CMYK)', 'nopea-media' ); ?></span>
							</label><br />
							<label>
                                <input type="text" name="secondry_color_cmyk"  value="<?=get_option('secondry_color_cmyk') ?? ''?>"
								placeholder="100,0,0,59" class="regular-text" required pattern="^\d{1,3},\d{1,3},\d{1,3},\d{1,3}$"> 
								<span class="description"><?php esc_html_e( 'Secondary color (CMYK)', 'nopea-media' ); ?></span>
							</label><br />
						</fieldset>
					</td>
				</tr>

				<?php if(defined('WP_DEBUG') && WP_DEBUG): ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'API endpoint', 'nopea-media' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="text" name="<?=sprintf('%s_service_url', NOPME_PREFIX)?>" value="<?=get_option(sprintf('%s_service_url', NOPME_PREFIX)) ?? ''?>" class="regular-text">
									<span class="description"><?php esc_html_e( 'Dev only', 'nopea-media' ); ?></span>
								</label><br />
							</fieldset>
						</td>
					</tr>
				<?php endif;?>		

				<tr>
					<th scope="row"><?php esc_html_e( 'API Key', 'nopea-media' ); ?></th>
					<td>
						<fieldset>
							<label>
                                <input type="text" name="<?=sprintf('%s_api_key', NOPME_PREFIX)?>" value="<?=get_option(sprintf('%s_api_key', NOPME_PREFIX)) ?? ''?>" class="regular-text" required>
								<span class="description"><a target="_blank" href="https://en.nopea.media/shop"><?php esc_html_e( 'Get Token', 'nopea-media' ); ?></a></span>
							</label><br />
						</fieldset>
					</td>
				</tr>
				
			</tbody>
		</table>
		
			<h3><?= __('This plugin can use the shortcodes of following plugins:') ?></h3>
			<ul style="list-style:inherit !important; padding: 10 !important; padding-left: 30px; font-size: 15px; font-type: bold;">
				<li> <a href="https://wordpress.org/plugins/m-chart/" target="_blank"> m-chart </a> </li>	
			</ul>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?= esc_html_e( 'Save Changes', 'nopea-media' ); ?>">
		</p>
	</form>
</div>