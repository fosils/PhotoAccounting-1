YUI().use('datatable-scroll', "datasource-io", "datasource-jsonschema",
		"datatable-datasource", function(Y) {
			var columns = [ {
				key : "Number",
				label : "No."
			}, 'Name', 'VAT' ];

			var dataSource = new Y.DataSource.IO({
				source : "php/economic-ajax.php"
			});

			dataSource.plug(Y.Plugin.DataSourceJSONSchema, {
				schema : {
					resultFields : [ "Number", "Name", {
						key : 'VAT',
						locator : "VatAccountHandle.VatCode"
					} ]
				}
			});
			
			calculateHeight = function(){
				var documentHeight=parseInt(Y.one("html").getComputedStyle('height'));
				var formHeight=parseInt(Y.one("#jsv_form").getComputedStyle('height'));
				var marginsAndPaddingsSum=110;			
				return documentHeight-formHeight-marginsAndPaddingsSum;
			};

			var table = new Y.DataTable({
				caption : "Accounts Data",
				columnset : columns,
				scrollable : "y",
				height : calculateHeight()
			}).plug(Y.Plugin.DataTableDataSource, {
				datasource : dataSource
			});

			table.render('#economicAccountsData');
			table.datasource.load();
		});