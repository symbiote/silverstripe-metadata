<div id="$Name" class="$CSSClasses $Type ss-metadatasetfield field">
	<% if Schemas %>
		<div id="$ID" class="ss-metdatasetfield">
			<% loop Schemas %>
				<h3><a href="$Link">$Title</a></h3>
				<div>
					<% if Description %>
						<p class="ss-metadatasetfield-description">$Description</p>
					<% end_if %>
					<% loop Fields %>
						$FieldHolder
					<% end_loop %>
				</div>
			<% end_loop %>
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
				<% loop Keywords %>
					<li><strong>$$Name</strong> - $Label</li>
				<% end_loop %>
			</ul>
			
			<h4>Member Keywords</h4>
			<p>
				These fields are read from the current member account, and are
				replaced with the corresponding value when the record is saved:
			</p>
			<ul>
				<% loop MemberKeywords %>
					<li><strong>&#36;Member.$Name</strong> - $Label</li>
				<% end_loop %>
			</ul>
		</div>
	<% else %>
		<p>There are no metadata schemas attached to this object.</p>
	<% end_if %>
</div>