<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div style="max-width: 560px;padding: 20px;background: #ffffff;border-radius: 5px;margin: 40px auto;font-family: Open Sans,Helvetica,Arial;font-size: 15px;color: #666">
    <div style="color: #444444;font-weight: normal">
        <div style="text-align: center;font-weight: 600;font-size: 26px;padding: 10px 0;border-bottom: solid 3px #eeeeee">{site_name}</div>
        <div style="clear: both"> </div>
    </div>
    <div style="padding: 0 30px 30px 30px;border-bottom: 3px solid #eeeeee">
        <div style="padding: 30px 0;font-size: 24px;text-align: center;line-height: 40px">The profile for <a href="{profile_url}" target="_blank" rel="noopener">{username}</a><br />has been updated by {updating_user} at {current_date}.</div>
        <div>{fields_updated}</div>
    </div>
    <div style="color: #999;padding: 20px 30px">
        <div>Thank you</div>
        <div>The <a style="color: #3ba1da;text-decoration: none" href="{site_url}">{site_name}</a> Team</div>
    </div>
</div>
