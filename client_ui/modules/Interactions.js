define([ "dojo/_base/declare", "dojo/dom", "dojo/on", "dojo/touch","dojo/topic",
		"dojo/mouse", "dojox/gesture/tap", "dojo/json", "dojo/_base/array",
		"dojo/_base/lang", "dojo/dom-geometry", "sl_modules/geometry/Point",
		"sl_modules/geometry/Rectangle", 'jslib/almeros/waterripple',
		"sl_modules/WAPI" ], function(declare, dom, domOn, touch, topic, mouse,
		dojoxTap, JSON, dojo_array, lang, domGeom, sl_point, sl_rect, sl_wapi) {

	return declare(null, {
		SINGLE_CLICK : 1,
		MULTI_CLICK : 2,
		MOUSE_MODE : 1,
		TOUCH_MODE : 2,
		lastAlt : false,
		lastShift : false,
		lastCtrl : false,
		startPoint : null,
		globalStartPoint : null,
		endPoint : null,
		globalEndPoint : null,
		deltaPoint : null,
		mode : null,
		interactionMode : 0,
		isInteracting : false,
		isPaused : false,
		targetPane : null,
		intervalId : null,
		hovering : false,
		dragging : false,
		dragOver : false,
		isOut : false,
		tapEnabled : false,
		touches : null,
		waterModel : null,
		waterCanvas : null,
		clickInterval : null,
		fingerCount : 0,

		constructor : function(targetPane) {
			// targetPane = targetPane.sl_map;

			// TODO: Figure out custom cursors.
			// CursorManager.removeAllCursors();
			// Mouse.show();
			// TODO: figure out doubleclick support

			domOn(targetPane, 'dblclick', this.DoubleClickHandler.bind(this));
			// domOn(targetPane, 'click', this.ClickHandler.bind(this));
			// domOn(targetPane, dojoxTap.doubletap, this.DoubleClickHandler
			// .bind(this));
			domOn(targetPane, touch.release, this.TouchRelease.bind(this));
			domOn(targetPane, touch.press, this.TouchHandler.bind(this));

			/*
			 * domOn(this.sl_map_img, 'dblclick', this.handleClick .bind(this));
			 * domOn(this.sl_map_img, 'click', this.DoubleClickHandler
			 * .bind(this)); domOn(this.sl_map_img, touch.press,
			 * this.handleTouchStart.bind(this)); domOn(this.sl_map_img,
			 * touch.move, this.handleTouchMove.bind(this));
			 * domOn(this.sl_map_img, touch.release,
			 * this.handleTouchRelease.bind(this)); domOn(this.sl_map_img,
			 * 'mouseout', this.handleLeave .bind(this));
			 * targetPane.doubleClickEnabled = true; //
			 * targetPane.addEventListener( MouseEvent.CLICK , // ClickHandler );
			 * targetPane.addEventListener(MouseEvent.DOUBLE_CLICK,
			 * DoubleClickHandler);
			 * targetPane.addEventListener(MouseEvent.MOUSE_DOWN,
			 * MouseDownHandler);
			 * targetPane.addEventListener(MouseEvent.MOUSE_MOVE,
			 * MouseMoveHandler);
			 * targetPane.addEventListener(MouseEvent.MOUSE_OUT,
			 * MouseOutHandler);
			 * targetPane.addEventListener(MouseEvent.MOUSE_OVER,
			 * MouseOverHandler);
			 * targetPane.addEventListener(MouseEvent.MOUSE_UP, MouseUpHandler);
			 * targetPane.addEventListener(MouseEvent.MOUSE_WHEEL,
			 * MouseWheelHandler);
			 * targetPane.addEventListener(MouseEvent.ROLL_OUT,
			 * MouseRollOutHandler);
			 * targetPane.addEventListener(MouseEvent.ROLL_OVER,
			 * MouseRollOverHandler);
			 */
			this.targetPane = targetPane;
			this.interactionMode = this.MULTI_CLICK;
			// this.hoverTimer = new Timer(1000 , 1 );
			// #this.hoverTimer.addEventListener(
			// TimerEvent.TIMER_COMPLETE , HoverHandler );
			this.dragOver = false;
			return this;
		},
		CancelInteraction : function(event) {
			this.isPaused = this.isInteracting = this.dragging = false;
			if (this.intervalId != null) {
				clearTimeout(this.intervalId);
				this.timeLeft = this.intervalId = null;
			}
			// dispatchEvent(new InteractionEvent(InteractionEvent.CANCEL, null,
			// null, event));
		},
		MouseDownHandler : function(event) {
			// if ( event.target == targetPane ) {
			if (dragging)
				return;
			var pt = EventToPoint(event.pageX, event.pageY);
			if (!pt)
				return;
			this.startPoint = pt;
			this.globalStartPoint = new Point(event.pageX, event.pageY);

			// lastAlt = event.altKey;
			// lastCtrl = event.ctrlKey;
			// lastShift = event.shiftKey;
			// isInteracting = true;
			// dispatchEvent(new InteractionEvent(InteractionEvent.START,
			// startPoint, null, event));

			// }
		},
		MouseUpHandler : function(event) {
			if (this.dragOver) {
				this.dragOver = false;
				return;
			}
			if (this.isOut)
				this.isOut = false;
			if (startPoint !== null) {
				if (!isInteracting && (globalStartPoint == null)) {
					isPaused = isInteracting = dragging = false;
					return;
				}

				endPoint = new Point(event.localX, event.localY);
				globalEndPoint = new Point(event.stageX, event.stageY);
				deltaPoint = new Point(globalEndPoint.x - globalStartPoint.x,
						globalEndPoint.y - globalStartPoint.y);
				if (this.dragging) {
					isInteracting = isPaused = false;
					dragging = false;
					endPoint = new Point(startPoint.x + deltaPoint.x,
							startPoint.y + deltaPoint.y);
					dispatchEvent(new InteractionEvent(InteractionEvent.DROP,
							startPoint, endPoint, event));
				}
				if ((Math.abs(deltaPoint.x) <= 2)
						&& (Math.abs(deltaPoint.y) <= 2) && dragging == false) {
					if (interactionMode == SINGLE_CLICK) {
						lastAlt = event.altKey;
						lastCtrl = event.ctrlKey;
						lastShift = event.shiftKey;
						isInteracting = isPaused = false;
						ClickHandler(event);
						startPoint = endPoint = null;
					}
				}
			}
			if (event.buttonDown && this.isPaused)
				this.isPaused = false;
		},
		DoubleClickHandler : function(event) {
			var pt = this.EventToPoint(event);
			if (!pt)
				return;
			event.preventDefault();
			// console.log(pt.ToWKT());

		},
		ClickHandler : function(point) {

			// alert(point.ToWKT());
			// dispatchEvent(new
			// InteractionEvent(InteractionEvent.CLICK,
			// startPoint, endPoint, event));
			// notify listeners: INTERACTION_CLICK
		},
		TouchHandler : function(event) {

			points = this.EventToPoints(event);

			if (points.length == 0)
				return;

			switch (this.mode) {
			case this.MOUSE_MODE:
				if (this.fingers == null) {
					this.fingers = [];
				}
				this.fingers.push(points[0]);
				break;
			case this.TOUCH_MODE:
				if (this.fingers == null) {
					this.fingers = [];
				}
				this.fingers.push( points[0]);
				break;
			}

		},
		TouchRelease : function(event) {
				
			if (this.fingers != null) {
				if (this.fingers.length > 0) {
					event.preventDefault(true);
					if (this.interactionMode == this.SINGLE_CLICK) {
						return this.HandleClicks();
					} else {
						return this.ResetClickTimer();
					}
				}
			}
	

			return;
		},
		ResetClickTimer : function() {
			clearTimeout(this.clickInterval);
			this.clickInterval = setInterval(this.HandleClickInterval
					.bind(this), 200);

		},
		ClearClickInterval : function() {
			if (this.clickInterval)
				clearTimeout(this.clickInterval);
			this.clickInterval = null;
		},
		HandleClickInterval : function() {
			this.ClearClickInterval();

			this.HandleClicks();
		},
		HandleClicks : function() {
			this.ClearClickInterval();
			//alert(this.fingers.length);
			topic.publish('sl_interaction/clicks',{points:this.fingers,target:this.targetPane})
			this.fingers = [];
			
		},
		EventToPoints : function(event) {
			if (this.mode === null) {
				this.mode = ('touches' in event) ? this.TOUCH_MODE
						: this.MOUSE_MODE;
			}
			points = [];

			switch (this.mode) {
			case this.TOUCH_MODE:
				for ( var i = 0; i < event.touches.length; i++) {
					touch = event.touches[i];

					var pt = this.EventToPoint(touch);

					if (pt)
						points.push(pt);
				}

				break;
			case this.MOUSE_MODE:
				var pt = this.EventToPoint(event);
				points.push(pt);
				break;
			}
			return points;
		},
		EventToPoint : function(event) {

			var position = domGeom.position(this.targetPane.sl_map_img);
			var rect = new sl_rect();

			rect.FromPointAndDims(position.x, position.y, position.w,
					position.h);
			point = new sl_point(event.pageX, event.pageY);
			if (!rect.IsWithin(point))
				return false;
			rect.AdjustPoint(point);
			return point;
		}

	});

}

)
