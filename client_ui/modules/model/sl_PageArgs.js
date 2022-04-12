/**
 * 
 */

define(["dojo/_base/declare"], function(declare){
  return  {
	  pageArgs:null,
	
	  GetPageArg:function(argName) { 
  		if (!this.pageArgs) return null;
		for( var arg in this.pageArgs) {
			if(arg.toLowerCase()==argName.toLowerCase()) {
				return this.pageArgs[arg];
			};
		}
		return null;
  	}
  }
});
