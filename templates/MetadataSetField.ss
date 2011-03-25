<div id="$Name" class="$CSSClasses $Type ss-metadatasetfield field">
	<div id="$ID" class="ss-metdatasetfield">
		<% control Schemas %>
			<h3><a href="#">$Title</a></h3>
			<div>
				<% if Description %>
					<p class="ss-metadatasetfield-description">$Description</p>
				<% end_if %>
				<% control Fields %>
					$FieldHolder
				<% end_control %>
			</div>
		<% end_control %>
	</div>
	
	<div class="ss-metadatasetfield-keywordreplacements">
		<h3>Available Keyword Replacements</h3>
		<p>
			Keywords in the metadata field content in the form "&#36;FieldName" will
			be replaced with the corresponding value from the record. The list
			of available keywords is:
		</p>
		<ul>
			<% control Keywords %>
				<li><strong>$$Name</strong> - $Label</li>
			<% end_control %>
		</ul>
	</div>
</div>