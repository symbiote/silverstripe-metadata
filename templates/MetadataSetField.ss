<div id="$Name" class="$CSSClasses $Type field">
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
</div>