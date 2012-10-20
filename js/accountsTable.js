YUI().use('datatable-scroll', "datasource-io", "datasource-jsonschema",
		"datatable-datasource", function(Y) {
	
			// formatter to display cell as input field
			var hotKeyFormatter = function(o) {  
			    var hotkey = new String(o.data.hotkey);  
			    var data = hotkey.toUpperCase().charCodeAt(0);
			    if(o.data.hotkey) return "<input type='text' maxlength='1' size='1' class='hotkey-input'  value='"+hotkey+"' data='"+data+"'/><span class='field_error'></span>";
			    else return;     
			};
			var columns = [ {
					key : "Number",
					label : "No."
				},
				'Name',
				'VAT', {
					key : "hotkey",
					label : "HotKey",
					allowHTML: true,
					formatter: hotKeyFormatter,
					emptyCellValue: "<input type='text' maxlength='1' size='1' class='hotkey-input'/><span class='field_error'></span>"
				}
			];

			var dataSource = new Y.DataSource.IO({
				source : "php/proxy.php?function=get_accounts"
			});

			dataSource.plug(Y.Plugin.DataSourceJSONSchema, {
				schema : {
					resultFields : [ "Number", "Name", {
						key : 'VAT',
						locator : "VatAccountHandle.VatCode"
					},
					"hotkey" ]
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

			window.accountsTable = table;
		});