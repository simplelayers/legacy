define(
		[ "dojo/_base/declare", "dijit/_WidgetBase", "dijit/_TemplatedMixin",
				"dijit/_WidgetsInTemplateMixin", "dijit/layout/ContentPane",
				'dojo/dom', "dojo/on", "dojo/touch","dojo/topic", "dojo/mouse",
				'dojo/dom-attr', 'sl_modules/sl_URL', 'sl_modules/Map',
				'sl_modules/Query', 'sl_modules/PxUtil',
				'jslib/almeros/waterripple',
				"dojo/text!sl_components/map_flat/templates/map.html" ],
		function(declare, _WidgetBase, _TemplatedMixin,
				_WidgetsInTemplateMixin, _ContentPane, dom, domOn, touch, topic,
				mouse, domAttr, sl_url, sl_map, sl_query, sl_pxUtil,
				waterripple, map_template) {

			return declare(
					'components/map_flat',
					[ _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin ],
					{
						// Some default values for our author
						// These typically map to whatever you're passing to the
						// constructor
						templateString : map_template,
						mapData : null,
						baseClass : "map_flat",
						mapOwner : null,
						timeLeft : null,
						intervalId : null,
						hoverPoint : null,
						fingers : [],
						lastPoint : null,
						constructor : function(params, srcRef) {
							this.inherited(arguments);
							this.mapOwner = params.addedBy;
							this.mapData = params.mapData;

							return this;

						},
						GetMap:function() {
							return sl_map;
						},
						GetMapData:function() {
							return this.mapData;
						},
						postCreate : function() {
							this.startup();
							this.inherited(arguments);
							
							
							
							/*domOn(this.sl_map_img, 'click', this.handleClick
									.bind(this));
							domOn(this.sl_map_img, touch.press,
									this.handleTouchStart.bind(this));
							domOn(this.sl_map_img, touch.move,
									this.handleTouchMove.bind(this));
							domOn(this.sl_map_img, touch.release,
									this.handleTouchRelease.bind(this));
							domOn(this.sl_map_img, 'mouseout', this.handleLeave
									.bind(this));
							*/
						},
						handleEnter : function(e) {

						},
						handleLeave : function(e) {
							this.stopHoverInterval();
							this.message.innerHTML = '';
						},
						handleHover : function() {
							/*var pxRect = sl_pxUtil.ptToBox(this.lastPoint.x,
									this.lastPoint.y, 3);
							alert(pxRect.toString());

							this.innerHTML = 'Hovered over ('
									+ this.lastPoint.toString() + ')'
									+ ": queryArea: (" + pxRect.toString()
									+ ")";
								*/
							// var pxPt =
							// sl_query.makePxPoint(e.clientX,e.clientY);
						},
						handleClick : function(e) {
							// TODO: send event map-clicked

						},

						handleTouchStart : function(e) {
							e.preventDefault();
							if (this.hoverStart) {
								if ((this.hoverStart == e.clientX)
										&& (this.hoverStart == e.clientY)) {
									return;
								}
							}
							this.resetHoverInterval()
							this.hoverStart = {
								x : e.clientX,
								y : e.clientY
							};
							this.lastPoint = pxUtil.makePxPt(e.clientX,
									e.clientY);
							this.innerHTML = 'Hover begin:'
									+ this.lastPoint.toString();
							this.fingers = e.touches;
							// TODO: Dispatcher event touchstart

						},
						handleTouchMove : function(e) {
							this.fingers = e.hasOwnProperty('touches') ? e.touches
									: [];
							this.resetHoverInterval();
							this.lastPoint = sl_pxUtil.makePxPt(e.clientX,
									e.clientY);

							/*
							 * this.message.innerHTML = 'num touches:' +
							 * (e.hasOwnProperty('touches')) ? e.touches.length :
							 * '0';
							 */
							e.preventDefault();
							// TODO: dispatch event touchmove
						},
						handleTouchRelease : function(e) {
							e.preventDefault();
							if (this.intervalId) {
								clearInterval(this.intervalId);
								this.timeLeft = null;
								this.intervalId = null;
							}

							if (!this.touches)
								this.touches = [];
							// this.message.innerHTML = 'num touches:'
							// + this.touches.length;
							// TODO: dispatch event touch release

						},
						handleHoverInterval : function() {

							if (this.intervalId) {
								if (this.timeLeft === null) {
									this.timeLeft = 10;
								}
								if (this.timeLeft === 0) {
									this.handleHover();
									this.stopHoverInterval();
									return;
								} else {
									this.timeLeft -= 1;
								}
								
								// this.message.innerHTML = this.intervalId+':
								// Hover will trigger in: .'+this.timeLeft+'
								// seconds at point '+this.lastPoint.toString();
							} else {
								this.message.innerHTML = '';
								// TODO: dispatch event handle hover.
							}

						},
						resetHoverInterval : function() {
							this.stopHoverInterval();

							this.timeLeft = 10;
							this.intervalId = setInterval(
									this.handleHoverInterval.bind(this), 100);

						},
						stopHoverInterval : function() {
							if (this.intervalId != null) {
								clearTimeout(this.intervalId);
								this.timeLeft = this.intervalId = null;
							}

						},
						getBBox : function() {
							if (!this.mapData)
								return false;
							return this.mapData.extents.projected;
						},

						render : function(e) {

							if (e)
								alert(e.target);
							var url = sl_url.getAPIPath();

							url += 'map/render/?'
							url += 'token=' + this.mapOwner.token;
							url += '&map=' + this.mapData.id;
							url += '&width=' + this.mapOwner.getWidth();
							url += '&height=' + this.mapOwner.getHeight();
							url += '&bbox=' + this.getBBox();
							url = sl_map.appendRenderURL(url, this.mapData,
									this.mapOwner.width, this.mapOwner.height,
									this.mapOwner.token);
							domAttr.set(this.sl_map_img, 'src', url);

						},
						startup : function() {
							this.inherited(arguments);
						}
					});
		});
