define(
		[],
		function() {
			return {
				SortAlphaNumeric : function(a, b) {
					var lastChar = '';
					for(var i=0 ; i < Math.min(a.length,b.length); i++ )
					{				
						if(a.substr(0,1)==b.substr(0,1)) {
							lastChar = a.substr(0,1);
							if(a.substr(0,1).match(/[a-zA-Z]/) || b.substr(0,1).match(/[a-zA-Z]/)) {
								a= a.substr(1);
								b=b.substr(1);
							} else {
								break;
							}
						} else {
							break;
						}
					}
					
					var reA;
					var reN;
					aMatch = a.match(/\./g);
					bMatch = b.match(/\./);
					aMatch = aMatch ? aMatch.length > 1 : false;
					bMatch = bMatch ? bMatch.length > 1 : false;
					if (aMatch || bMatch) {
						reA = /[^a-zA-Z\.\!\"\#\$\%\&\'\(\)\*\+\,\/\:\;\<\=\>\?\@\[\\\]\^\_\`\{\|\}\~\]]/g;
						reN = /[^-0-9]/g;
					} else {
						reA = /[^a-zA-Z\!\"\#\$\%\&\'\(\)\*\+\,\/\:\;\<\=\>\?\@\[\\\]\^\_\`\{\|\}\~\]]/g;
						reN = /[^-0-9\.]/g;
					}
					var AInt = parseFloat(a, 10);
					var BInt = parseFloat(b, 10);
					
					var aA;
					var bA;
					aStr = (a.substr(0,1).match(/[a-zA-Z]/));
					bStr = (b.substr(0,1).match(/[a-zA-Z]/));
					aStr = aStr ? aStr.length>0 : false;
					bStr = bStr ? bStr.length>0 : false;
					
					if(aStr && !bStr) return 1;
					
					if(bStr && !aStr) return -1;
					if(aStr && bStr) {
						aA = a.replace(reA, "");
						bA = b.replace(reA, "");
						if(aA != bA) {
							return aA > bA ? 1 : -1;
						}
						AInt = Math.abs(parseFloat(a.replace(reN,'')));
						BInt = Math.abs(parseFloat(b.replace(reN,'')));
						
						
					}
					if(!aStr && !bStr) {
						if(lastChar.match(/[a-zA-Z]/)) {
							AInt = Math.abs(AInt);
							BInt = Math.abs(BInt);
						}
					}
					if (AInt === BInt) {
						aA = a.replace(reA, "");
						bA = b.replace(reA, "");
						return aA > bA ? 1 : -1;

					} else if (isNaN(AInt)) {
						return 1;// to make alphanumeric sort first return -1
									// here
					} else if (isNaN(BInt)) {// B is not an Int
						return -1;// to make alphanumeric sort first return 1
									// here
					} else {
						return AInt > BInt ? 1 : -1;
					}
				},
				sort_by:function(field,sort){
					var key = function(data) {return data[field];};
					return function (a, b) {
				       return sort( key(a), key(b));
				    };
				}
			};
		});