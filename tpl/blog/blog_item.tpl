{include file="header.tpl"}


    
      <div class="row">

        <div class="col-sm-8 blog-main">


		{if $internal_referrer}
		<ul class="pager">
  			<li class="previous"><a class="back" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog">&larr; Back</a></li>
		</ul>
		{/if}

		<h2 class="blog-post-title">{$blog->heading}</h2>
		<p class="blog-post-meta">{$blog->stamp|sdate:$def_date_format}</p>

		{$blog->body|replace:"</p>":"</p>\n"}


		{*{include file="elib:/blog/comp_social_buttons.tpl"}*}

		{*{include file="elib:/blog/comp_disqus.tpl"}*}


		{if 0 and $connect}
		<p>Sign in with twitter to leave comments.</p>
		<p><a href="#" id="connect">
		   <img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/elib/lighter.png" alt="Sign in with Twitter"/></a></p>
		{/if}

		{section name=comment_item loop=$comments}
		<div class="entry">

		{*<h2>{$comments[comment_item].heading} <span>|</span> {$comments[comment_item].stamp|date_format:"%d/%m/%Y, %k:%M"} <span>|</span> {$comments[comment_item].username}</h2>*}

		<h4>{$comments[comment_item].username} wrote<br />on {$comments[comment_item].stamp|date_format:"%A %e %B %Y at %k:%M"}</h4>
		{$comments[comment_item].body|replace:"</p>":"</p>\n"}
		</div>
		{/section}

		{if 0 and $user_id > 0}

		{if sizeof($errors) > 0}
		<ul id="error">
		{foreach from=$errors item=error}
		<li>{$error}</li>
		{/foreach}
		</ul>
		{/if}

		<form action="" method="post">
		<fieldset><legend></legend>
		<p>
		<label>Heading</label>
		<input type="text" name="heading" value="{$comment->heading}" />
		</p>
		<p>
		<label>Body</label>
		<textarea rows="0" cols="0" name="body">{$comment->body|replace:'</p><p>':"\r\n"|replace:'<p>':""|replace:'</p>':""}</textarea>
		</p>

		<p>
		<label>&nbsp;</label>
		<button type="submit" name="submit">Submit</button>
		</p>
		</fieldset>
		</form>
		{/if}
                
                {*
   
          <nav>
            <ul class="pager">
              <li><a href="#">Previous</a></li>
              <li><a href="#">Next</a></li>
            </ul>
          </nav>
*}

        </div><!-- /.blog-main -->

        {include file="elib:/blog/sidebar.tpl"}

      </div><!-- /.row -->


{include file="footer.tpl"}
