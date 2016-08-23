<div class="cb-col col$SortOrder $ClassName $ExtraCssClass<% if $VerticalAlign %> v-align<% end_if %>" id="cb-block-{$ID}">
	<div class="cb-col-inside i-{$Align.LowerCase}"<% if $CustomStyling %> style="$CustomStyling"<% end_if %>>
		<% if $Link.URL %>
			<a href="$Link.URL" $Link.TargetATT>
		<% end_if %>	
				$Contents
		<% if $Link.URL %>
			</a>
		<% end_if %>
	</div>
</div>