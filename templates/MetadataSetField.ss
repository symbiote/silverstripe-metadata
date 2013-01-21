<div id="$Name" class="$CSSClasses $Type ss-metadatasetfield field">
	<% if Schemas %>
		<div id="$ID" class="ss-metdatasetfield">
			<% control Schemas %>
				<h3><a href="$Link">$Title</a></h3>
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
			
			<h4>Record Keywords</h4>
			<ul>
				<% control Keywords %>
					<li><strong>$$Name</strong> - $Label</li>
				<% end_control %>
			</ul>
			
			<h4>Member Keywords</h4>
			<p>
				These fields are read from the current member account, and are
				replaced with the corresponding value when the record is saved:
			</p>
			<ul>
				<% control MemberKeywords %>
					<li><strong>&#36;Member.$Name</strong> - $Label</li>
				<% end_control %>
			</ul>
		</div>
	<% else %>
		<p>There are no metadata schemas attached to this object.</p>
	<% end_if %>
</div>