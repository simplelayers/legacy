<div id="subnav"></div></div>
<div style="left:0;right:0;padding:0;">
<div id="messagingUsers" style="padding:0;float:left;width:24%;">
</div>
<div id="messageMain">
	<form action=<"./?do=wapi.contact.message" method="post">
		<table style="width:100%">
			<tr>
				<td style="width:80%;height:84px;">
					<textarea name="message" style="width:100%;height:100%;"></textarea>
				</td>
				<td style="width:20%;height:84px;">
					<input type="submit" value="Send Message" style="width:100%;height:100%;"/>
				</td>
			</tr>
		</table>
		<input type="hidden" value="" id="userId" name="recipient" />
		<input type="hidden" value="send" name="action" />
	</form>
	<div id="messagesBody">
		
	</div>
</div>
<script>
	$(function() {
		if( $('#subnav') !== null) {$('#subnav').parent().css({margin:0,padding:0});}
		$(".filterNav").prependTo("#navRow");
	});
	var messages = <!--{$messages}-->;
	var unread = <!--{$unread}-->;
	var userInfo = <!--{$userInfo}-->;
	var total = <!--{$total}-->;
	var displayCount = [];
	$(function(){
		var toSelect = false;
		$.each(total,  function(index, value){
			displayCount[parseInt(value.from)] = 30;
		});
		$.each(userInfo,  function(index, value){
			if(value[3]){
				var wasSet = false;
				$.each(unread,  function(index2, value2){
					if(value2.from == index){
						addUser(index, parseInt(value2.messages));
						wasSet = true;
					}
				});
				if(!wasSet){
					addUser(index, 0);
				}
				if(!toSelect) toSelect = index;
			}
		});
		if(location.hash){
			toSelect = window.location.hash.substring(2);
		}
		$('#'+toSelect).click();
		setWidth();
		if($('#'+toSelect).length == 0){$('#messageMain').html('<div class="messageError">No messages to display. Try selecting a contact from <a href="./?do=contact.list">your contacts list</a>.</div>');}
	});
	$(window).resize(function() {
		setWidth();
	});
	function setWidth(){
		$('#messageMain').css('min-height',$('#messagingUsers').outerHeight() - parseInt($('#messageMain').css('margin-top')) - parseInt($('.messagingUser').first().css('margin-top')) - (parseInt($('#messageMain').css('border-width'))*2));
	}
	function addUser(id, isunread){
		var item = $('<div class="messagingUser" id="'+id+'"><a class="closeMessage" href="./?do=wapi.contact.message&action=closemessage&recipient='+id+'"><img src="media/icons/delete.png"/></a>'+(isunread>0 ? '<a class="unreadUser" id="unread'+id+'">'+isunread+'</a>' : '')+'<a class="nameUrl" href="#U'+id+'">'+userInfo[id][0]+'</a></div>').click(clickUser);
		var afterItem = false;
		var isCompairUnread = false;
		$('.messagingUser').each(function (index){
			var toCheck = $(this).find('.nameUrl').text();
			var toCheckUnread = $(this).id;
			$.each(unread, function(index2, value2){
				if(value2.from == toCheckUnread){
					if(parseInt(value2.messages) != 0){
						isCompairUnread = true;
					}
				}
			});
			if((toCheck.localeCompare(userInfo[id][0]) == -1 && ((isCompairUnread && isunread) || (!isCompairUnread && !isunread))) || (isCompairUnread && !isunread)) afterItem = $(this);
		});
		if(afterItem) afterItem.after(item);
		else $('#messagingUsers').prepend(item);
	}
	function clickUser(eventObject){
		var id = $(this).attr('id');
		window.location.hash = '#U'+id;
		$('#unread'+id).remove();
		if(!$(this).hasClass("messageingUserSelected")){
			$('.messagingUser').removeClass("messageingUserSelected");
			$(this).addClass("messageingUserSelected");
			$('#messagesBody').html('');
			$('#userId').val(id);
			buildMessages(id);
		}
		var max = 0;
		if(id in messages){
			if(typeof messages[id][0] != 'undefined') max = messages[id][0].id;
		}
		$.getJSON('./?do=wapi.contact.message&action=mark',{recipient:id, upto:max},function(data) {
			$.each(data, function(key, value) {
				var item = $('<div></div>');
				if(value.from == "<!--{$user->id}-->"){ item.addClass("toMessage");
				}else{item.addClass("fromMessage");}
				item.addClass("message");
				item.html(value.message);
				item.prepend(getCard(parseInt(value.from), value.read, value.sent));
				
				$('.messageError').remove();
				$('#messagesBody').append(clear);
				$('#messagesBody').prepend(item);
				$('#messagesBody').append($('.moreButton'));
			});
		});
	}
	var clear = $('<div style="clear:both;"></div>');
	function buildMessages(id){
		$('.messageError').remove();
		$.each(messages[id], function(index, value){
			var item = $('<div></div>');
			if(value.from == "<!--{$user->id}-->"){ item.addClass("toMessage");
			}else{item.addClass("fromMessage");}
			item.addClass("message");
			item.html(value.message);
			item.prepend(getCard(parseInt(value.from), value.read, value.sent));
			
			$('#messagesBody').append(item);
			$('#messagesBody').append(clear);
		});
		if(messages[id].length){
			$.each(total, function(index, value){
				if(parseInt(value.from) == id){
					if(displayCount[id] < parseInt(value.messages)){
						var showMore = $('<div></div>');
						showMore.addClass("moreButton");
						showMore.html('<button type="button">Show More Messages.</button>');
						showMore.find('button').click(function(){
							var id = $('#userId').val();
							$.getJSON('./?do=wapi.contact.message&action=more',{recipient:id, offset:messages[id][messages[id].length-1].id},function(data) {
								$.each(data, function(key, value) {
									var item = $('<div></div>');
									if(value.from == "<!--{$user->id}-->"){ item.addClass("toMessage");
									displayCount[parseInt(value.to)] +=1;
									}else{item.addClass("fromMessage");
									displayCount[parseInt(value.from)] +=1;}
									item.addClass("message");
									item.html(value.message);
									item.prepend(getCard(parseInt(value.from), value.read, value.sent));
									messages[id].push(value);
									$('#messagesBody').append(item);
									$('#messagesBody').append(clear);
									$('#messagesBody').append($('.moreButton'));
									$.each(total, function(index, value){
										if(parseInt(value.from) == id){
											if(displayCount[id] >= parseInt(value.messages)) $('#messagesBody').find('.moreButton').remove();
										}
									});
								});
							});
						});
						$('#messagesBody').append(showMore);
					}
				}
			});
		}else{
			$('#messagesBody').append('<div class="messageError">No messages to display.</div>');
		}
	}
	function getCard(id, read, date){
		var name;
		var icon;
		var date;
		var read;
		if(id == <!--{$user->id}-->){
			name = "You";
			icon = "./?do=contact.icon&id=<!--{$user->id}-->";
			date = date;
			read = read;
		}else{
			name = userInfo[id][0];
			icon = userInfo[id][2];
			date = date;
			read = read;
		}
		return $('<div class="userCard"><span class="read">'+read+'</span><img src="'+icon+'"/><span class="name">'+name+'<br/><span class="date">'+date+'</span></span></div>');
	}
</script>