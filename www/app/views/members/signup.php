<h1><?php echo __('login_message'); ?></h1>
<p><?php echo __('already_member'); ?> <a href='<?php echo SITEURL;?>members/login'><?php echo __('login'); ?></a>

<?php
echo Error::display($error);
?>

<form action='' method='post' style="width: 500px">
    <div class="form-group">
        <label for="userName"><?php echo __('userName'); ?></label>
        <input type="text" class="form-control" id="userName" name="userName" placeholder="<?php echo __('userName'); ?>" value="<?php if(!empty($error)){ echo $_POST['userName']; } ?>">
    </div>

    <div class="form-group">
		<label for="firstName"><?php echo __('firstName'); ?></label>
		<input type="text" class="form-control" id="firstName" name="firstName" placeholder="<?php echo __('firstName'); ?>" value="<?php if(!empty($error)){ echo $_POST['firstName']; } ?>">
	</div>

	<div class="form-group">
		<label for="lastName"><?php echo __('lastName'); ?></label>
		<input type="text" class="form-control" id="lastName" name="lastName" placeholder="<?php echo __('lastName'); ?>" value="<?php if(!empty($error)){ echo $_POST['lastName']; } ?>">
	</div>

	<div class="form-group">
		<label for="email">Email</label>
		<input type="text" class="form-control" id="email" name="email" placeholder="Email" value="<?php if(!empty($error)){ echo $_POST['email']; } ?>">
	</div>

	<div class="form-group">
		<label for="password"><?php echo __('password'); ?></label>
		<input type="password" class="form-control" id="password" name="password" placeholder="<?php echo __('password'); ?>" value="">
	</div>

	<div class="form-group">
		<label for="passwordConfirm"><?php echo __('confirm_password'); ?></label>
		<input type="password" class="form-control" id="passwordConfirm" name="passwordConfirm" placeholder="<?php echo __('confirm_password'); ?>" value="">
	</div>

    <div class="form-group">
        <label><input name="tou" id="tou" type="checkbox" value="1" /> <?php echo __('tou'); ?></label>
        <label><input name="sof" id="sof" type="checkbox" value="1" /> <?php echo __('sof'); ?></label>
    </div>

	<input type="hidden" name="csrfToken" value="<?php echo $data['csrfToken']; ?>" />

	<div class="form-group">
		<div class="g-recaptcha" data-sitekey="<?php echo ReCaptcha::getSiteKey() ?>"></div>
	</div>

	<button type="submit" name="submit" class="btn btn-primary"><?php echo __('signup'); ?></button>
</form>

<script src="https://www.google.com/recaptcha/api.js?hl=<?php echo Language::code()?>" async defer></script>

<div class="sof_block">
    <div class="sof_content">
        <header class="text-center">
            <div class="entry-header-title">
                <h1 itemprop="headline">Statement of Faith</h1>
            </div>
        </header>

        <div class="page-content row">
            <div class="col-md-10 col-md-offset-1">
                <p><em>The following statement of faith is subscribed to by all member
                        organizations of and contributors to the <a href="https://unfoldingword.org" target="_blank">unfoldingWord</a> project. It
                        is in agreement with the <a href="http://www.lausanne.org/en/documents/lausanne-covenant.html" title="http://www.lausanne.org/en/documents/lausanne-covenant.html" target="_blank">Lausanne Covenant</a>.</em></p>

                <p>We believe that Christian belief can and should be divided into
                    <strong>essential beliefs</strong> and <strong>peripheral beliefs</strong>.</p>

                <h3 id="essential-beliefs">Essential beliefs</h3>

                <p>Essential beliefs are what define a follower of Jesus Christ and can
                    never be compromised or ignored.</p>

                <ul>
                    <li>We believe the Bible to be the only inspired, inerrant, sufficient,
                        authoritative Word of God.</li>
                    <li>We believe that there is one God, eternally existent in three
                        persons: God the Father, Jesus Christ the Son and the Holy Spirit.</li>
                    <li>We believe in the deity of Jesus Christ.</li>
                    <li>We believe in the humanity of Jesus Christ, in His virgin birth, in
                        His sinless life, in His miracles, in His vicarious and atoning
                        death through His shed blood, in His bodily resurrection, in His
                        ascension to the right hand of the Father.</li>
                    <li>We believe that every person is inherently sinful and so is
                        deserving of eternal hell.</li>
                    <li>We believe that salvation from sin is a gift of God, provided
                        through the sacrificial death and resurrection of Jesus Christ,
                        attained by grace through faith, not by works.</li>
                    <li>We believe that true faith is always accompanied by repentance and
                        regeneration by the Holy Spirit.</li>
                    <li>We believe in the present ministry of the Holy Spirit by whose
                        indwelling the follower of Jesus Christ is enabled to live a godly
                        life.</li>
                    <li>We believe in the spiritual unity of all believers in the Lord Jesus
                        Christ, from all nations and languages and people groups.</li>
                    <li>We believe in the personal and physical return of Jesus Christ.</li>
                    <li>We believe in the resurrection of both the saved and the lost; the
                        unsaved will be resurrected to eternal damnation in hell and the
                        saved will be resurrected to eternal blessing in heaven with God.</li>
                </ul>

                <h3 id="peripheral-beliefs">Peripheral beliefs</h3>

                <p>Peripheral beliefs are everything else that is in Scripture but about
                    which sincere followers of Christ may disagree (e.g. Baptism, Lord’s
                    Supper, the Rapture, etc.). We choose to agree to disagree agreeably on
                    these topics and press on together toward a common goal of making
                    disciples of every people group (Matthew 28:18-20).</p>

                <input type="button" class="btn btn-success" id="sof_agree" value="<?php echo __('accept_btn'); ?>" />
                <input type="button" class="btn btn-danger" id="sof_cancel" value="<?php echo __('deny_btn'); ?>" />

                <br /><br />
            </div>
        </div>
    </div>
</div>

<div class="tou_block">
    <div class="tou_content">
        <header class="text-center">
            <div class="entry-header-title">

                <h1 itemprop="headline">Freedom</h1>

            </div>
        </header>

        <div class="page-content row">
            <div class="col-md-10 col-md-offset-1">
                <p>All content in the unfoldingWord project is made available under a
                    <a href="http://creativecommons.org/licenses/by-sa/4.0/" target="_blank">Creative Commons Attribution-ShareAlike 4.0 License</a>.</p>

                <p>You are free to:</p>

                <ul>
                    <li><strong>Share</strong> — copy and redistribute the material in any medium or format</li>
                    <li><strong>Adapt</strong> — remix, transform, and build upon the material for any purpose, even commercially</li>
                </ul>

                <p>Under the following conditions:</p>

                <ul>
                    <li><strong>Attribution</strong> — You must attribute the work as follows: “Original work available at <a href="https://unfoldingword.org" target="_blank">unfoldingWord.org</a>.”
                        Attribution statements in derivative works should not in any way suggest that we endorse you or your use of this work.</li>
                    <li><strong>ShareAlike</strong> — If you remix, transform, or build upon the material, you must distribute your contributions under
                        the same license as the original.</li>
                </ul>

                <p>Use of trademarks: <strong>unfoldingWord™</strong> is a trademark of Distant Shores Media and may not be included on any derivative
                    works created from this content. Unaltered content from <a href="https://unfoldingword.org" target="_blank">unfoldingWord.org</a> must include the unfoldingWord logo when
                    distributed to others. If you alter the content in any way, you must remove the unfoldingWord logo before distributing your work.</p>

                <p>For contributors to projects in Door43 or unfoldingWord, use this form:</p>

                <p><a class="btn btn-dark" href="https://unfoldingword.org/assets/docs/legal/unfoldingWord - Guidelines &amp; License.pdf" target="_blank">Download Guidelines &amp; License PDF</a></p>

                <p>For release of existing content under CC BY-SA, use this form:</p>

                <p><a class="btn btn-dark" href="https://unfoldingword.org/assets/docs/legal/unfoldingWord - License Release Form.pdf" target="_blank">Download License Release PDF</a></p>

                <input type="button" class="btn btn-success" id="tou_agree" value="<?php echo __('accept_btn'); ?>" />
                <input type="button" class="btn btn-danger" id="tou_cancel" value="<?php echo __('deny_btn'); ?>" />

                <br /><br />
            </div>
        </div>
    </div>
</div>