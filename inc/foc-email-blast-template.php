<?php
/**
 * FOC Email Blast Template
 *
 * Renders an Email Template based on the Fields filled
 * in FOC Site Settings > Email Blast Tempalte
 * Found on FOC 
 *
 * @package Salient WordPress Theme
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$container_size       = 750;
$content_wrapper_size = 690;
$header_image         = get_field( 'header_image', 'options' );
$header_image_size    = array( $container_size, null );
$image_attrs          = array( 'style'=>'width:auto;height:auto;max-width: 100%;' );
$header_image         = $header_image['id'] ?
  wp_get_attachment_image( $header_image['id'], $header_image_size, false, $image_attrs ) :
  null;
$foc_primary_color    = '#08677d';
$default_button_text  = 'Read the full story here';

ob_start();
?>

<div id="copyTarget" class="main" align="center" style="text-align:center;width:100%;background-image: url(https://followourcourts.com/wp-content/uploads/foc-email-blast-bg-min.jpg);background-size: cover;background-repeat: no-repeat;background-position: 50% 0%;">
  <span class="preview-text" style="display:none;font-size:0px;line-height:0px;max-height:0px;max-width:0px;opacity:0;overflow:hidden">This Week in Inland Empire Legal News</span>
  <div style="background-color: #08677d;width:100%;">
		<?php if ( $header_image ) : ?>
			<div style="width:750px;margin-left:auto;margin-right:auto;max-width:100%;padding-bottom: 10px; text-align:center;" align="center">
				<div style="text-align: center;">
					<a href="https://followourcourts.com" style="color: #08677d; max-width: 100%;">
						<?php echo $header_image; ?>
					</a>
				</div>
			</div>
		<?php endif; // endif ( $header_image ) : ?>
  </div>
  <!--[if mso]>
 <div align="center"style="text-align:center;">
 <table style="width:100%;" width="100%"><tr><td width="750">
<![endif]-->

  <div style="width:750px;margin-left:auto;margin-right:auto;max-width:100%;text-align: center;background-color: #fff;" align="center">
    <table style="border-collapse: collapse;width: 750px;margin-left:auto;margin-right:auto;max-width:100%;" border="0" width="750" cellspacing="0" cellpadding="0">
      <tbody>

				<?php
				if ( have_rows( 'email_body_fc', 'options' ) ) :
					while ( have_rows( 'email_body_fc', 'options' ) ) :
						the_row();
						$row_layout      = get_row_layout();
						$row_index       = get_row_index();

						// Story Category Title
						if ( $row_layout == 'story_category_title' ) :
							$category_title   = esc_html( get_sub_field( 'category_title' ) );
							// First Category Divider's Border-Top needs to be transparent.
							$cat_conditional_styles = $row_index === 1 ?
								'border-top: 5px solid transparent;padding: 0 40px 40px 40px;margin-top: 40px;' :
								"border-top: 5px solid $foc_primary_color;padding: 40px;margin-top: 40px;";
							if ( $category_title ) :
								?>
								<tr class="category-divider">
									<td style="border-color: #e9e9e9 transparent;">
										<div class="category-title-wrapper" style="border-bottom: 5px solid #08677d;<?php echo $cat_conditional_styles; ?>">
											<h1 style="text-align: center; font-size: 40px; margin: 0px; color: #333; font-family: Lato, open-sans, Helvetica, Arial, sans-serif;" class="category-title"><?php echo $category_title; ?></h1>
										</div>
									</td>
								</tr>
								<?php
							endif; // endif ( $category_title ) :
						endif; // endif ( $row_layout == 'story_category_title' ) :
						// /Story Category Title

						// Story Layout Type
						// Get story_layout_type_fc flexible field for later use.
						$story_layout_type_fc = get_sub_field( 'story_layout_type_fc' );
						if ( $story_layout_type_fc ) :
							while ( has_sub_field( 'story_layout_type_fc' ) ) :
								$sub_row_layout    = get_row_layout();
								$sub_row_index     = get_row_index();
								$button_text       = $default_button_text;
								$story_image_size  = array( $content_wrapper_size, null );
								$story_image_attrs = array( 'style', 'width: auto;height: auto;max-width: 100%;max-height: 100%;' );
								$story_separator = $sub_row_index < count($story_layout_type_fc) ?
									"border-bottom: 3px solid $foc_primary_color;":
									null;

								// Story Layout Type - Dynamic
								if ( $sub_row_layout == 'dynamic' ) :
									$story = get_sub_field( 'story' );
									if ( $story[0] ) :
										$story_title   = esc_attr( $story[0]->post_title );
										$story_author  = get_author_name( $story[0]->post_author );
										$story_author  = "By: $story_author";

										$featured_image_width = get_sub_field( 'featured_image_width' );
										// If featured_image_width has been provided
										// Use IT as the $story_image_size width instead.
										$story_image_size = $featured_image_width ?
											array( $featured_image_width, $story_image_size[1] ) :
											$story_image_size;
										$story_image   = get_the_post_thumbnail(
											$story[0]->ID,
											$story_image_size,
											$story_image_attrs,
										);
										$yoast_meta    = get_post_meta($story[0]->ID, '_yoast_wpseo_metadesc', true);
										$story_content = $yoast_meta ? : ( $story[0]->post_excerpt ? : wp_trim_excerpt( '', $story[0] ) );
										$story_link    = get_the_permalink( $story[0] );
									endif; // endif ( $story[0] ) :
								endif; // endif ( $sub_row_layout == 'dynamic' ) :
								// /Story Layout Type - Dynamic

								// Story Layout Type - Static
								if ( $sub_row_layout == 'static' ) :
									$story_title    = wp_kses_post( get_sub_field( 'title', false ) );
									$story_author   = wp_kses_post( get_sub_field( 'author', false ) );
									$featured_image = get_sub_field( 'featured_image' );

									$featured_image_width = get_sub_field( 'featured_image_width' );
									// If featured_image_width has been provided
									// Use IT as the $story_image_size width instead.
									$story_image_size = $featured_image_width ?
										array( $featured_image_width, $story_image_size[1] ) :
										$story_image_size;
									$story_image      = $featured_image ?
										wp_get_attachment_image(
											$featured_image['id'],
											$story_image_size,
											false,
											$story_image_attrs
										) :
										null;
									$story_content    = wp_kses_post( get_sub_field( 'description', false ) );
									$link_to_post     = esc_url( get_sub_field( 'link_to_post' ) );
									$story_link       = $link_to_post;
									$button_text      = esc_attr( get_sub_field( 'button_text' ) );
								endif; // endif ( $sub_row_layout == 'static' ) :
								// /Story Layout Type - Static

								// Render Story
								?>
								<tr class="story">
									<td style="padding: 30px;">
										<div class="story-inner" style="padding-bottom: 30px;<?php echo $story_separator; echo 'display:block;' ?>">
											<?php if ( $story_title ) : ?>
												<h2 style="text-align: center; font-size: 40px; line-height: 1.15; color: #08677d; margin-bottom: 0.25em; font-family: Lato, open-sans, Helvetica, Arial, sans-serif; margin-top: 1.5em;" class="story-title"><?php echo $story_title; ?></h2>
												<?php
											endif; // endif ( $story_title ) :
											if ( $story_author ) :
												?>
												<h3 style="text-align: center; font-size: 20pt; margin-top: 0px; font-family: 'Lato', 'open-sans', Helvetica, Arial, sans-serif; color: #333;" class="story-author"><?php echo $story_author; ?></h3>
												<?php
											endif; // endif ( $story_author ) :
											if ( $story_image ) :
												?>
												<div style="color: #333; text-align: center; max-width: 100%;" class="story-featured-image"><?php echo $story_image; ?></div>
												<?php
											endif; // endif ( $story_image ) :
											if ( $story_content ) :
												?>
												<div class="story-content" style="font-family: Lato, open-sans, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; text-align: left;">
													<div class="story-description" style="font-size: 25px;"><?php echo $story_content; ?></div>
													<?php if ( $story_link && $button_text ) : ?>
														<div class="story-cta">
															<table border="0" cellpadding="0" cellspacing="0" style="border-collapse:separate!important;border-radius:0px;background-color:#08677d;margin-top:15px" class="table-cta">
																<tbody>
																	<tr>
																		<td align="center" valign="middle" style="font-family: Lato, open-sans, Helvetica, Arial, sans-serif;font-size:25px;padding: 12.5px 25px;">
																			<a class="anchor-cta" title="Read this story on followourcourts.com" href="<?php echo $story_link; ?>" style="font-weight:bold;letter-spacing:normal;line-height:100%;text-align:center;text-decoration:none;color:#ffffff;display:block" target="_blank"><?php echo $button_text; ?></a>
																		</td>
																	</tr>
																</tbody>
															</table>
														</div>
													<?php endif; // endif ( $story_link || $button_text ) : ?>
												</div>
												<?php
											endif; // endif ( $story_content ) :
											?>
										</div>
									</td>
								</tr>
								<?php
								// /Render Story

							endwhile; // endwhile ( $row_layout == 'story_layout_type_fc' ) :
						endif; // endif ( $story_layout_type_fc ) :
						// /Story Layout Type

					endwhile; // endwhile ( have_rows( 'email_body_fc', 'options' ) ) :

					$html = ob_get_clean();
					echo $html;
				endif; // endif ( have_rows( 'email_body_fc', 'options' ) ) :

				// Table Body End and start Table Footer.
				?>

      </tbody>
      <tfoot>
        <tr>
          <td style="border-collapse: collapse;border: 0px;margin: 0px;font-family: Lato, open-sans, Helvetica, Arial, sans-serif;font-size: 16px;line-height: 26px;background-color: #08677d;color: #fff;padding: 30px;">
            <div style="text-align:center;">
              <p>Please, whitelist <b>Follow Our Courts</b> in your email preferences<br>to avoid missing the news you care about.</p>
            </div>
          </td>
          <td>
          </td>
        </tr>
        <tr>
          <td style="border-collapse: collapse; border: 0px; margin: 0px; color: #333; font-family: Lato, open-sans, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px;">
            <div style="text-align:center;margin-top:16px;">
              <p>As always, commentary and letters to the editor are welcome at <a href="mailto:tcm@followourcourts.com" style="color: #08677d">tcm@followourcourts.com</a></p>
            </div>
          </td>
        </tr>

        <tr>
          <td style="border-collapse: collapse; border: 0px; margin: 0px; color: #333; font-family: Lato, open-sans, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; text-align: center;">
            <p><b>Follow our social media channels <br>to receive Follow Our Courts news first.</b></p>
          </td>
        </tr>
        <tr>
          <td valign="top" align="center" style="border-collapse: collapse; border: 0px; margin: 0px; color: #333; font-family: Lato, open-sans, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px;">
            <table style="font-weight: normal;border-collapse: collapse;border: 0;margin: 0;padding: 0;font-family: Arial, sans-serif">
              <tbody>
                <tr>
                  <td align="center" valign="middle" class="social" style="border-collapse: collapse; border: 0px; margin: 0px; color: #333; font-family: Lato, open-sans, Helvetica, Arial, sans-serif; font-size: 20px; line-height: 1.25; text-align: center;">
                    <table style="font-weight: normal;border-collapse: collapse;border: 0;margin: 0;padding: 0;font-family: Arial, sans-serif" class="social-icons">
                      <tbody>
                        <tr>
                          <td style="border-collapse: collapse; border: 0px; margin: 0px; color: #333; font-family: Lato, open-sans, Helvetica, Arial, sans-serif; font-size: 20px; line-height: 1.25; padding: 1em;" valign="center"><a href="https://followourcourts.com/feed/rss/" title="Follow Our Court RSS Feed" style="color: #08677d;"><img src="https://info.tenable.com/rs/tenable/images/rss-teal.png"></a></td>
                          <td style="border-collapse: collapse; border: 0px; margin: 0px; color: #333; font-family: Lato, open-sans, Helvetica, Arial, sans-serif; font-size: 20px; line-height: 1.25; padding: 1em;" valign="center"><a href="https://twitter.com/followourcourts" title="Visit the Follow Our Courts Twitter Account" style="color: #08677d;"><img src="https://info.tenable.com/rs/tenable/images/twitter-teal.png"></a></td>
                          <td style="border-collapse: collapse; border: 0px; margin: 0px; color: #333; font-family: Lato, open-sans, Helvetica, Arial, sans-serif; font-size: 20px; line-height: 1.25; padding: 1em;" valign="center"><a href="https://www.facebook.com/Follow-Our-Courts-108429204885961" title="Visit the Follow Our Courts Facebook Page" style="color: #08677d;"><img src="https://info.tenable.com/rs/tenable/images/facebook-teal.png"></a></td>
                          <td style="border-collapse: collapse;border: 0px;margin: 0px;color: #333;font-family: Lato, open-sans, Helvetica, Arial, sans-serif;font-size: 20px;line-height: 1.25;padding: 1em;" valign="center"><a href="https://www.youtube.com/channel/UCHLelcd2VdgJagOdj6NHKtw" title="Visit the Follow Our Courts YouTube Channel" style="color: #08677d;"><img src="https://info.tenable.com/rs/tenable/images/youtube-teal.png"></a></td>
                          <td style="border-collapse: collapse; border: 0px; margin: 0px; color: #333; font-family: Lato, open-sans, Helvetica, Arial, sans-serif; font-size: 20px; line-height: 1.25; padding: 1em;" valign="center"><a href="https://www.linkedin.com/company/follow-our-courts/" title="Visit the Follow Our Courts LinkedIn Company Page" style="color: #08677d;"><img src="https://info.tenable.com/rs/tenable/images/linkedin-teal.png"></a></td>

                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
        <tr>
          <td style="background-color: #08677d;padding: 30px;">
            <div style="font-size: 15px;font-family: 'Lato', 'open-sans', Helvetica, Arial, sans-serif;color: #fff;text-align: center;">
              <p>Copyright © <?php echo get_year_func() ?> Follow Our Courts, all rights reserved.</p>
              <p style="text-align:center">This free resource is brought to you by <a href="https://mccunewright.com" target="_blank" style="white-space: nowrap;">McCune Law Group</a>, 
							<span style="display:inline-block;">McCune Wright Arevalo</span> <span style="display:inline-block;">Vercoski Kusel Weck Brandt,</span> <span style="display:inline-block;">APC</span></p>
            </div>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
  <!--[if mso]>
 </td></tr></table>
 </div>
<![endif]-->
</div>

<div id="copy-to-clipboard-wrapper" style="text-align: center;padding: 60px;">
	<button id="copyToClipBoard" class="button button-primary button-large" type="button" style="font-size: 30px;font-weight: bold;">Copy to Clipboard</button>
</div>
<script id="copy-to-clipboard-inline-js" type="text/javascript">
	var copyTarget = document.getElementById("copyTarget");
	function commandExecCopy(copyTarget) {
			// create hidden text element, if it doesn't already exist
			var targetId = "_hiddenCopyText_";
			var isInput = copyTarget.tagName === "INPUT" || copyTarget.tagName === "TEXTAREA";
			var origSelectionStart, origSelectionEnd;
			if (isInput) {
					// can just use the original source element for the selection and copy
					target = copyTarget;
					origSelectionStart = copyTarget.selectionStart;
					origSelectionEnd = copyTarget.selectionEnd;
			} else {
					// must use a temporary form element for the selection and copy
					target = document.getElementById(targetId);
					if (!target) {
							var target = document.createElement("textarea");
							target.style.position = "absolute";
							target.style.left = "-9999px";
							target.style.top = "0";
							target.id = targetId;
							document.body.appendChild(target);
					}
					// target.textContent = copyTarget.textContent;
					target.textContent = copyTarget.outerHTML;
			}
			// select the content
			var currentFocus = document.activeElement;
			target.focus();
			target.setSelectionRange(0, target.value.length);
			
			// copy the selection
			var succeed;
			try {
					succeed = document.execCommand("copy");
			} catch(e) {
					succeed = false;
			}
			// restore original focus
			if (currentFocus && typeof currentFocus.focus === "function") {
					currentFocus.focus();
			}
			
			if (isInput) {
					// restore prior selection
					elem.setSelectionRange(origSelectionStart, origSelectionEnd);
			} else {
					// clear temporary content
					target.textContent = "";
			}
			return succeed;
	}
	function navCopyToClipBoard(copyTarget) {
		navigator.clipboard.writeText(copyTarget.outerHTML);
	}
	function copyToClipBoard() {
		if (! navigator.clipboard){
			// use old commandExec() way
			commandExecCopy(copyTarget);
		} else{
			navCopyToClipBoard(copyTarget);
		}
	}
	document.getElementById("copyToClipBoard").addEventListener("click", function() {
		copyToClipBoard();
	});
</script>