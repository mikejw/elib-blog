

{*<p><a href="http://instagram.com/mikeyjw"><img id="me" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/img/mikewhiting.jpg" alt="" /></a></p>*}


<div id="categories">
<h4>Category</h4>

<!-- @todo: put font-awesome icons in CMS -->

<ul class="clear">
{foreach from=$categories item=c}
<li>
{if $c.label eq 'Technology'}
<i class="fa fa-gear"></i>

{elseif $c.label eq 'Music'}
<i class="fa fa-music"></i>
{elseif $c.label eq 'Other'}
<i class="fa fa-plug"></i>
{elseif $c.label eq 'Photography'}
<i class="fa fa-picture-o"></i>
{elseif $c.label eq 'Any'}
<i class="fa fa-random"></i>

{/if}


{if $c.id eq $blog_category}
{$c.label}
{else}

<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/category/{$c.label|lower}/">{$c.label}</a>

{*<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/set_category/{$c.label|lower}/">{$c.label}</a>*}


{/if}
</li>
{/foreach}
</ul>
</div>



{if isset($archive)}

<div id="archive">

<h4>Archive</h4>

{if sizeof($archive) lt 1}
<p>None found.</p>
{else}
<nav>
<ul>
{foreach from=$archive item=y key=year}
<li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$year}">{$year}</a>
	{*<ul{if $year eq $blog->stamp|date_format:"%Y"} class="current"{/if}>*}
	<ul{if $year eq $current_year || $year eq $blog->stamp|date_format:"%Y"} class="current"{/if}>
	{foreach from=$y item=m key=month}
	<li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$year}/{$month|substr:0:3|lower}">{$month}</a>
		<ul{if ($month eq $blog->stamp|date_format:"%B" && $year eq $blog->stamp|date_format:"%Y") ||
		($month eq $current_month && $year eq $current_year)} class="current"{/if}>
		{foreach from=$m item=b key=id}
		{if $id neq $blog->id}
                
                {if $b.slug neq ''}
                <li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$year}/{$b.month_slug}/{$b.day}/{$b.slug}">{$b.heading}</a></li>
                {else}
		<li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/item/{$id}">{$b.heading}</a></li>
                {/if}
                
		{else}

		<li>{$b.heading}</li>

		{/if}
		{/foreach}
		</ul>
	</li>
	{/foreach}
	</ul>
</li>
{/foreach}
</ul>
</nav>
{/if}
</div>
{/if}


<div class="tags">
<div id="tags_collapsible" class="clear">

{section name=tag_item loop=$tags}
<a style="font-size:{$tags[tag_item].share}em;" {if is_array($active_tags) && in_array($tags[tag_item].tag, $active_tags)}class="active" {/if}href="http://{$WEB_ROOT}{$PUBLIC_DIR}/tags/{if $active_tags_string eq ''}{$tags[tag_item].tag}{elseif in_array($tags[tag_item].tag, $active_tags)}{$active_tags_string|regex_replace:$tags[tag_item].tag_esc_1:''|regex_replace:$tags[tag_item].tag_esc_2:''|replace:$tags[tag_item].tag:''}{else}{$active_tags_string}+{$tags[tag_item].tag}{/if}">{$tags[tag_item].tag}</a>
{/section}

</div>
</div>

<p>
{*<a href="#">&nbsp;<span>Show</span> Tags</a>*}

</p>


{*
<div class="tags">
<div class="clear">
<span>Links:</span> 
<span><a href="http://twitter.com/mikejw">twitter.com/mikejw</a></span>
<span><a href="http://uk.linkedin.com/in/mikejw">uk.linkedin.com/in/mikejw</a></span>
<span><a href="http://www.facebook.com/mikewhiting">facebook.com/mikewhiting</a></span>
</div>
</div>

<div class="tags">
<div class="clear">
<span>Contact:</span> 
<span>mail@mikejw.co.uk</span>
</div>
</div>
*}



{*
<p>&nbsp;</p>
<img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/img/mikejw_caricature.png" alt="" />
*}



