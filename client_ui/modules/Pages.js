define(
	["dojo/dom", "dojo/dom-attr", "dojo/json", "dojo/_base/lang",
		"dojo/cookie", "sl_modules/sl_URL", "dojo/_base/xhr",
		"sl_modules/Permissions",
		"sl_modules/model/sl_PageArgs"

	],
	function (dom, domAttr, json, lang, dojo_cookies, sl_url, xhr, Permissions, sl_PageArgs) {
		return {
			baseURL: sl_url.getServerPath(),
			ACTOR_ADMIN: 'admin',
			ACTOR_USER: 'user',
			ACTOR_NONUSER: 'nonuser',
			pageArgs: null,
			permissions: null,
			pageNav: null,
			GoTo: function (page, params, newWin) {

				var url = this.baseURL;

				if (url.substr(-1, 1) != '/')
					url += "/";
				url += page;
				if (page == '?') {
					if (params) {
						for (var key in params) {
							url += '&' + key + '=' + params[key];
						}
					}
				} else {
					if (params) {
						for (var key in params) {
							url += '/' + key + ':' + params[key];
						}
					}
				}
				if (!newWin) {
					window.location.href = url;
				} else {
					win = window.open(url, newWin);
					win.focus();
				}
			},
			GoToURL: function (url, newWin) {
				if (url.indexOf('javascript:') === 0) {
					// alert(url);
					// return;
				}
				if (url.trim().indexOf('wapi/v5/') > 0) {
					var env = sl_url.getSvcsURL();
					var parts = url.trim().split('/v5/');
					var path = env + parts.pop();
					let a = document.createElement("a");
					a.href = path;
					a.download = ""; // Suggests download instead of navigation
					document.body.appendChild(a);
					a.click();
					document.body.removeChild(a);
					// Optionally, remove the iframe after a short delay

					return;
				}
				if (!newWin) {
					window.location.href = url;
				} else {
					win = window.open(url, newWin);
					win.focus();
				}
			},
			GetPagePath: function (relPath) {
				var url = this.baseURL;
				if (url.substr(-1, 1) != '/') {
					url += "/";
				}
				url += relPath;
				return url;
			},
			GetPageActor: function () {
				return this.GetPageArg('pageActor');
			},
			GetPageArg: function (argName) {
				if (!this.pageArgs)
					return null;
				if (argName === 'token') {
					return dojo_cookies('SLSESSID');

				}
				for (var arg in this.pageArgs) {
					if (arg.toLowerCase() == argName.toLowerCase()) {
						return this.pageArgs[arg];
					}
				}
				return null;
			},
			SetPageArg: function (argName, argValue) {
				if (this.pageArgs == null) this.pageArgs = {};
				this.pageArgs[argName] = argValue;
			},
			RemovePageArg: function (argName) {
				if (!this.pageArgs) {
					return null;
				}
				delete this.pageArgs[argName];
			},
			SetPageData: function (relPath, cookieData) {

				for (var i in cookieData) {
					this.SetPageDataVal(i, cookieData[i], 1, this.GetPagePath(relPath));
				}
			},
			MergePageData: function (pageArgs) {

				this.pageArgs = pageArgs;
				//console.log(pageArgs);
				var prefix = 'page__';
				var ca = document.cookie.split(';');

				for (var key in ca) {
					data = ca[key].split('=');

					if (data[0].indexOf(prefix) == 1) {
						var arg = data[0].substr(prefix.length + 1);

						this.pageArgs[arg] = decodeURI(data[1]).replace('%40', '@');
						dojo_cookies(data[0], '', { 'expires': 'Thu, 01 Jan 1970 00:00:01 GMT', 'path': '/' });
					}
				}
				this.permissions = pageArgs['permissions'];
				this.sl_permissions = Permissions;
				this.sl_permissions.SetPermissions(this.permissions);
				return this;
				return "";

			},
			SetPageDataVal: function (cname, cvalue, exdays, path) {
				var d = new Date();
				d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
				var expires = "expires=" + d.toGMTString();
				dojo_cookies('page__' + cname, cvalue, { 'expires': expires, 'path': '/' });

			},
			DeletePageData: function (cname) {

			},
			MeetsRequirements: function (reqs) {
				if (reqs.perm) {
					if (reqs.perm != '') {
						if (!this.sl_permissions) return false;
						permName = reqs.perm.split(':');

						if (permName[0] == '') permName.shift();
						if (permName.length == 0) {
							hasPermission = this.sl_permissions.HasPermission(reqs.perm, this.sl_permissions.VIEW);
							negate = false;
							if (permName[0].substr(0, 1) == "!") {
								permName[0] = permName[0].substr(1);
								negate = true;
							}
							if (negate) hasPermission = !hasPermission;
							if (!hasPermission) return;
						} else {

							var perm = permName.pop();
							permName = ':' + permName.join(":");
							negate = false;
							if (permName[0].substr(0, 1) == "!") {
								permName[0] = permName[0].substr(1);
								negate = true;
							}

							perms = perm.split('|');
							permitted = true;
							for (var i in perms) {
								perm = perms[i];
								permNegate = false;
								if (perm.substr(0, 1) == '!') {
									perm = perm.substr(1);
									permNegate = true;
								}
								val = this.sl_permissions.StrToPermValue(perm);
								if (!(val === null)) {

									hasPermission = this.sl_permissions.HasPermission(permName, val);
									if (permNegate) hasPermission = !hasPermission;
									if (negate) hasPermission = !hasPermission;
									//console.log(permName,perm,hasPermission);
									if (!hasPermission) return;
								}
							}
						}
					}

				}

				if (reqs.pageArg) {
					var arg = reqs.pageArg;


					var notArg = false;

					if (reqs.pageArg.substr(0, 1) == "!") {
						arg = reqs.pageArg.substr(1);
						theArg = (this.GetPageArg(arg));
						notArg = true;
						if (theArg !== null) return false;
					}

					var argValue = this.GetPageArg(reqs.pageArg);
					if (reqs.pageArgValue == 'false') notArg = true;
					if (reqs.pageArgValue == false) notArg = true;


					if (!notArg) {
						if ((argValue === null)) return false;
						if (argValue === false) return false;
						if (argValue === 'false') return false;
					}


					if (reqs.pageArgValue != null) {
						if ((reqs.pageArgValue === true) || (reqs.pageArgValue === false)) {
							if (notArg == (argValue == reqs.pageArgValue)) return false;
						} else {
							if (('' + argValue).toLowerCase() != ('' + reqs.pageArgValue).toLowerCase()) return false;
						}
					}
				}

				if (reqs.pageActor) {
					if (reqs.pageActor.substr(0, 1) == '!') {
						var pageActor = reqs.pageActor.substr(1);

						if (pageActor == this.GetPageArg('pageActor')) return false;
					} else {
						if (this.GetPageArg('pageActor') != reqs.pageActor) return false;
					}
				}

				if (reqs.orgActor) {
					if (this.GetPageArg('orgActor') != reqs.orgActor) {
						if (reqs.orgActor.length) {
							if (reqs.orgActor.substr(0, 1) == '!') {
								if (reqs.orgActor.substr(1) == this.GetPageArg('orgActor')) return false;
							} else {
								return false;
							}
						}
					}
				}
				if (reqs.contactActor) {
					if (this.GetPageArg('contactActor') != reqs.contactActor) {
						if (reqs.contactActor.length) {
							if (reqs.contactActor.substr(0, 1) == '!') {
								if (reqs.contactActor.substr(1) == this.GetPageArg('contactActor')) return false;
							} else {
								return false;
							}
						}
					}
				}
				if (reqs.groupActor) {
					if (this.GetPageArg('groupActor') != reqs.groupActor) {
						if (reqs.groupActor.length) {
							if (reqs.groupActor.substr(0, 1) == '!') {
								if (reqs.groupActor.substr(1) == this.GetPageArg('groupActor')) return false;
							} else {
								return false;
							}
						}
					}
				}

				return true;
			},
			SetPageNav: function (nav) {
				this.pageNav = nav;
			},
			MakeRelPath: function (slURL) {
				if (slURL.toLowerCase().indexOf("http://") == 0) {
					return slURL;
				}
				if (slURL.toLowerCase().indexOf("https://") !== 0) return slURL;
				dirs = slURL.split('/');
				dirs.shift(); // remove https:
				dirs.shift(); // remove empty dir from https:// <--
				domain = dirs.shift().toLowerCase().split('.');
				if (domain[0] == 'dev') {
					sandbox = dirs.shift();
					dirs.shift(); // get ridof simplelayers dirname
				}
				return dirs.join('/');
			}
		};

	});