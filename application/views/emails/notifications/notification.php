<div style="margin:0; padding:0; margin:0 auto;">
    <div id="wrapp" style="background-color:#111111; margin: 0 auto; width: 600px;">
        <div class="header">
            <a href="<?=$web_url;?>" alt="Battlepro.com" title="Battlepro: A new era for Gaming Competition"><img src="<?=$image_url;?>/email/header-btp.jpg"></a>
        </div>
        <div class="content" style="padding: 20px 75px;">
            <h2 style="color:#fff; font-size:20px; font-family:arial;">You have new notifications</h2>
            <?php if(isset($message)and !empty($message)):?>
                <div style="color:#ccc; font-size:15px; font-family:arial; line-height: 1.5;" class="well">
                    <?php echo $message;?>
                </div>
            <?php else:?>
            <p style="color:#ccc; font-size:15px; font-family:arial; line-height: 1.5;">
                Hi again, you have new notifications in your inbox.
            </p>
            <?php endif?>
         
            <div style="border-top: 1px solid #333; margin-top:40px; padding: 5px 0 30px 0; width: 100%;">
                <img src="<?=$image_url;?>/email/btp-sign.png" alt="Battlepro" title="Battlepro" style="float:right;">
            </div>
        </div>
        <div class="footer" style="background-color:#333; float:left; padding: 0 75px; width: 450px;">
            <div class="tv" style="float:left;">
                <a href="#" alt="Battlepro.Tv" title="Battlepro.Tv"><img src="<?=$image_url;?>/email/tv.png"></a>
            </div>
            <div class="contact" style="float:left; border-bottom: 3px solid #93bc0c; margin-top:2px;">
                <div class="box-left" style="float:left; border-left: 1px solid #1a1a1a; margin-left: 10px; margin-right:10px; padding: 0 0 0 10px; height:48px;">
                    <p><a href="<?=$web_url;?>" style="color:#93bc0c; font-size:14px; font-family:arial; text-decoration:none;" title="Battlepro.com">www.battlepro.com </a></p>
                    <p><a href="#" style="color: #ccc; font-size:14px; font-family:arial; text-decoration:none;" title="Contact us">contact@battlepro.com </a></p>
                </div>
                <div class="box-left" style="float:left; border-left: 1px solid #1a1a1a; margin-left: 5px; margin-right:5px; padding: 0 10px 0 10px; height:66px;">
                    <p style="color:#fff; font-size:13px; font-family:arial; margin:2px 0; padding: 2px 0;">Follow Us</p>
                    <a href="#" style="float:left; margin-right:5px; padding:2px 0 5px 0;" title="Facebook"><img src="<?=$image_url;?>/email/fb.png"></a>
                    <a href="#" style="float:left; margin-right:5px; padding:2px 0 5px 0;" title="twitter"><img src="<?=$image_url;?>/email/tw.png"></a>
                    <a href="#" style="float:left; margin-right:5px; padding:2px 0 5px 0;" title="Google Plus"><img src="<?=$image_url;?>/email/gp.png"></a>
                    <a href="#" style="float:left; margin-right:5px; padding:2px 0 5px 0;" title="Youtube"><img src="<?=$image_url;?>/email/yt.png"></a>
                </div>
            </div>
        </div>
    </div>
</div>