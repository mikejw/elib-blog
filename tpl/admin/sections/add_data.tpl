
<form action="" method="get">
<fieldset>
<legend>Add Data</legend>
<p>
<label>Data Type</label>

<span class="radios">
{html_radios name="data_type" options=$data_types separator="<br />"}
</span>


</p>
<p id="containers" class="hidden">
<label>Container Type</label>
<select name="container_type">
{html_options options=$container_types}
</select>      
</p>
<p>
<label>&nbsp;</label>
<input type="hidden" name="id" value="{if $class eq 'data_item'}{$data_item->id}{else}{$section_item->id}{/if}" />
<button type="submit" name="add">Add</button> 
<button type="submit" name="cancel">Cancel</button>
</p>
</fieldset>
</form>