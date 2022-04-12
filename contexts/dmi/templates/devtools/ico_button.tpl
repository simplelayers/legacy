

<style>
#iconset  {
	padding:0px;
	margin:0px;
}

#iconset td {
padding:0px;
margin:0px !important;

}
</style>


<script type="text/javascript">
<!--{literal}-->
var targetIcon = "";
var targetSize = "";

$(function() {
	$('td img.weblay_ico').click(function(event) {
		var title = event.currentTarget.tooltipText;
		 setIcon(title);
		
	});
	$('#sizeSelector').change(function(event) {
		setIcon(targetIcon);
	});
	$('#colorSelector').change(function(event) {
		setIcon(targetIcon);
	});
	$('#sampleLabel').keyup(function(event){
		setIcon(targetIcon);
	});
	setIcon('pencil');
});

function setIcon(iconName) {
	targetIcon = iconName;
	var sampleButtonImg  =$('#sampleButton img');
	sampleButtonImg.removeClass();
	
	sampleButtonImg.addClass("weblay_ico "+iconName);
	var  sampleButton = $('#sampleButton');
	sampleButton.removeClass();
	var color = $("#colorSelector").val();
	var buttonClass = "color button ico_button " + color;
	var size = $("#sizeSelector").val();
	$("#sampleButton #label").removeClass();
	var labelText = $("#sampleLabel").val();
	if( labelText=="") labelText="";
	$("#sampleButton #label").text(labelText);
	if(labelText !="") {
		$("#sampleButton #label").addClass("label");
	} else {
		buttonClass+=" noLabel";
	}
	buttonClass += ' '+size;
	console.log(buttonClass);
	sampleButton.addClass(buttonClass);
	var smarty = "<!--{ico_button color='"+color+"'";
	if( size != "") smarty+=" size='"+size+"'";
	smarty+=" icon='"+targetIcon+"'";
	smarty+="}-->"+labelText+"<!--{/ico_button}-->";
	$("#smarty").val(smarty);
}

function updateSmarty() {
	
}

<!--{/literal}-->
</script>
<table border="0">
<tr  >
<td style="vertical-align:middle">
Label: <input type="text" id="sampleLabel" value="This is a test"></input></td>
<td  style="vertical-align:middle"><select id="sizeSelector">
<option value="" selected="selected">large</option>
<option value="sm">Small</option>
</select>
</td>
<td  style="vertical-align:middle">
<select id="colorSelector">
<option value="normal" selected="selected">Normal</option>
<option value="red">Red</option>
<option value="green">Green</option>
<option value="blue">Blue</option>
</select>
</td>
<td  style="vertical-align:middle;text-align:right;">
<button id="sampleButton" class="color button ico_button"><img src="media/empty.png" class="weblay_ico"></img><span id="label" class="label"></span></button>
</td>
</tr>
<tr>
<td colspan="4">
Smarty:<Br>
<textarea style="width:100%" id="smarty">
</textarea>
</td>
</tr>
</table>
<table id="iconset" border="0"  >
<tr>
<td><img src="media/empty.png" class="weblay_ico pencil" title="pencil" ></img></td>
<td><img src="media/empty.png" class="weblay_ico brush" title="brush" ></img></td>
<td><img src="media/empty.png" class="weblay_ico dib"  title="dib"></img></td>
<td><img src="media/empty.png" class="weblay_ico dib_pen"  title="dib_pen"></img></td>
<td><img src="media/empty.png" class="weblay_ico brush_wide"  title="brush_wide"></img></td>
<td><img src="media/empty.png" class="weblay_ico paint_bucket"  title="paint_bucket"></img></td>
<td><img src="media/empty.png" class="weblay_ico eye_open"  title="eye_open"></img></td>
<td><img src="media/empty.png" class="weblay_ico univ_no"  title="univ_no"></img></td>
<td><img src="media/empty.png" class="weblay_ico trash"  title="trash"></img></td>
<td><img src="media/empty.png" class="weblay_ico mag"  title="mag"></img></td>
<td><img src="media/empty.png" class="weblay_ico mag_minus"  title="mag_minus"></img></td>
<td><img src="media/empty.png" class="weblay_ico mag_plus"  title="mag_plus"></img></td>
<td><img src="media/empty.png" class="weblay_ico wand"  title="wand"></img></td>
<td><img src="media/empty.png" class="weblay_ico crosshair"  title="crosshair"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico flag" title="flag" ></img></td>
<td><img src="media/empty.png" class="weblay_ico paperclip" title="paperclip" ></img></td>
<td><img src="media/empty.png" class="weblay_ico connections"  title="connections"></img></td>
<td><img src="media/empty.png" class="weblay_ico link"  title="link"></img></td>
<td><img src="media/empty.png" class="weblay_ico tag"  title="tag"></img></td>
<td><img src="media/empty.png" class="weblay_ico lock"  title="lock"></img></td>
<td><img src="media/empty.png" class="weblay_ico lock_open"  title="lock_open"></img></td>
<td><img src="media/empty.png" class="weblay_ico pushpin"  title="pushpin"></img></td>
<td><img src="media/empty.png" class="weblay_ico pin"  title="pin"></img></td>
<td><img src="media/empty.png" class="weblay_ico badge"  title="badge"></img></td>
<td><img src="media/empty.png" class="weblay_ico key"  title="key"></img></td>
<td><img src="media/empty.png" class="weblay_ico fire"  title="fire"></img></td>
<td><img src="media/empty.png" class="weblay_ico target"  title="target"></img></td>
<td><img src="media/empty.png" class="weblay_ico bolt"  title="bolt"></img></td>
<td><img src="media/empty.png" class="weblay_ico clock"  title="clock"></img></td>
<td><img src="media/empty.png" class="weblay_ico clock_tics"  title="clock_tics"></img></td>
<td><img src="media/empty.png" class="weblay_ico hour_glass"  title="hour_glass"></img></td>

</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico phone" title="phone" ></img></td>
<td><img src="media/empty.png" class="weblay_ico cell" title="cell" ></img></td>
<td><img src="media/empty.png" class="weblay_ico envelope"  title="envelope"></img></td>
<td><img src="media/empty.png" class="weblay_ico envelope_open"  title="envelope_open"></img></td>
<td><img src="media/empty.png" class="weblay_ico envelope_stuffed"  title="envelope_stuffed"></img></td>
<td><img src="media/empty.png" class="weblay_ico box_inout"  title="box_inout"></img></td>
<td><img src="media/empty.png" class="weblay_ico box_out"  title="box_out"></img></td>
<td><img src="media/empty.png" class="weblay_ico box_in"  title="box_in"></img></td>
<td><img src="media/empty.png" class="weblay_ico video"  title="video"></img></td>
<td><img src="media/empty.png" class="weblay_ico tape"  title="tape"></img></td>
<td><img src="media/empty.png" class="weblay_ico comment_filled"  title="comment_filled"></img></td>
<td><img src="media/empty.png" class="weblay_ico comment_round"  title="comment_round"></img></td>
<td><img src="media/empty.png" class="weblay_ico comment_sq"  title="comment_sq"></img></td>
<td><img src="media/empty.png" class="weblay_ico conversation"  title="conversation"></img></td>
<td><img src="media/empty.png" class="weblay_ico css_rev"  title="css_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico css"  title="css"></img></td>
<td><img src="media/empty.png" class="weblay_ico antenna"  title="antenna"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico contacts" title="contacts" ></img></td>
<td><img src="media/empty.png" class="weblay_ico contactbook" title="contactbook" ></img></td>
<td><img src="media/empty.png" class="weblay_ico person"  title="person"></img></td>
<td><img src="media/empty.png" class="weblay_ico person_tie"  title="person_tie"></img></td>
<td><img src="media/empty.png" class="weblay_ico person_minus_simple"  title="person_minus_simple"></img></td>
<td><img src="media/empty.png" class="weblay_ico person_plus"  title="person_plus"></img></td>
<td><img src="media/empty.png" class="weblay_ico person_minus"  title="person_minus"></img></td>
<td><img src="media/empty.png" class="weblay_ico person_x"  title="person_x"></img></td>
<td><img src="media/empty.png" class="weblay_ico contact"  title="contact"></img></td>
<td><img src="media/empty.png" class="weblay_ico people"  title="people"></img></td>
<td><img src="media/empty.png" class="weblay_ico buildings"  title="buildings"></img></td>
<td><img src="media/empty.png" class="weblay_ico calendar_day"  title="calendar_day"></img></td>
<td><img src="media/empty.png" class="weblay_ico calendar_month"  title="calendar_month"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico page" title="page" ></img></td>
<td><img src="media/empty.png" class="weblay_ico page_content" title="page_content" ></img></td>
<td><img src="media/empty.png" class="weblay_ico pages"  title="pages"></img></td>
<td><img src="media/empty.png" class="weblay_ico folder"  title="folder"></img></td>
<td><img src="media/empty.png" class="weblay_ico folder_open"  title="folder_open"></img></td>
<td><img src="media/empty.png" class="weblay_ico folder_locked"  title="folder_locked"></img></td>
<td><img src="media/empty.png" class="weblay_ico folder_plus"  title="folder_plus"></img></td>
<td><img src="media/empty.png" class="weblay_ico folder_minus"  title="folder_minus"></img></td>
<td><img src="media/empty.png" class="weblay_ico page_plus"  title="page_plus"></img></td>
<td><img src="media/empty.png" class="weblay_ico page_edit"  title="page_edit"></img></td>
<td><img src="media/empty.png" class="weblay_ico page_get"  title="page_get"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico home" title="home" ></img></td>
<td><img src="media/empty.png" class="weblay_ico home_rev" title="home_rev" ></img></td>
<td><img src="media/empty.png" class="weblay_ico signposts"  title="signposts"></img></td>
<td><img src="media/empty.png" class="weblay_ico world"  title="world"></img></td>
<td><img src="media/empty.png" class="weblay_ico map"  title="map"></img></td>
<td><img src="media/empty.png" class="weblay_ico marker"  title="marker"></img></td>
<td><img src="media/empty.png" class="weblay_ico sign"  title="sign"></img></td>
<td><img src="media/empty.png" class="weblay_ico preserver"  title="preserver"></img></td>
<td><img src="media/empty.png" class="weblay_ico code"  title="code"></img></td>
<td><img src="media/empty.png" class="weblay_ico objects"  title="objects"></img></td>
<td><img src="media/empty.png" class="weblay_ico object"  title="object"></img></td>
<td><img src="media/empty.png" class="weblay_ico network"  title="network"></img></td>
<td><img src="media/empty.png" class="weblay_ico stack"  title="stack"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico cart_solid" title="cart_solid" ></img></td>
<td><img src="media/empty.png" class="weblay_ico cart" title="cart" ></img></td>
<td><img src="media/empty.png" class="weblay_ico purse"  title="purse"></img></td>
<td><img src="media/empty.png" class="weblay_ico basket"  title="basket"></img></td>
<td><img src="media/empty.png" class="weblay_ico truck"  title="truck"></img></td>
<td><img src="media/empty.png" class="weblay_ico clipboard"  title="clipboard"></img></td>
<td><img src="media/empty.png" class="weblay_ico gift"  title="gift"></img></td>
<td><img src="media/empty.png" class="weblay_ico credit"  title="credit"></img></td>
<td><img src="media/empty.png" class="weblay_ico cash"  title="cash"></img></td>
<td><img src="media/empty.png" class="weblay_ico calculator"  title="calculator"></img></td>
<td><img src="media/empty.png" class="weblay_ico bank"  title="bank"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico gear" title="gear" ></img></td>
<td><img src="media/empty.png" class="weblay_ico gears" title="gears" ></img></td>
<td><img src="media/empty.png" class="weblay_ico tools"  title="tools"></img></td>
<td><img src="media/empty.png" class="weblay_ico screwdriver"  title="screwdriver"></img></td>
<td><img src="media/empty.png" class="weblay_ico wrench"  title="wrench"></img></td>
<td><img src="media/empty.png" class="weblay_ico toolbox"  title="toolbox"></img></td>
<td><img src="media/empty.png" class="weblay_ico wall_switch"  title="wall_switch"></img></td>
<td><img src="media/empty.png" class="weblay_ico sliders"  title="sliders"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico notebook" title="notebook" ></img></td>
<td><img src="media/empty.png" class="weblay_ico bookmarked" title="bookmarked" ></img></td>
<td><img src="media/empty.png" class="weblay_ico spiral_notes"  title="spiral_notes"></img></td>
<td><img src="media/empty.png" class="weblay_ico notebook_2"  title="notebook_2"></img></td>
<td><img src="media/empty.png" class="weblay_ico camera"  title="camera"></img></td>
<td><img src="media/empty.png" class="weblay_ico video_camera"  title="video_camera"></img></td>
<td><img src="media/empty.png" class="weblay_ico graph_bar"  title="graph_bar"></img></td>
<td><img src="media/empty.png" class="weblay_ico graph_line"  title="graph_line"></img></td>
<td><img src="media/empty.png" class="weblay_ico graph"  title="graph"></img></td>
<td><img src="media/empty.png" class="weblay_ico graphs"  title="graphs"></img></td>
<td><img src="media/empty.png" class="weblay_ico presentation_screen"  title="presentation_screen"></img></td>
<td><img src="media/empty.png" class="weblay_ico film"  title="film"></img></td>
<td><img src="media/empty.png" class="weblay_ico package_minus"  title="package_minus"></img></td>
<td><img src="media/empty.png" class="weblay_ico package_get"  title="package_get"></img></td>
<td><img src="media/empty.png" class="weblay_ico package_put"  title="package_put"></img></td>
<td><img src="media/empty.png" class="weblay_ico music_notes"  title="music_notes"></img></td>
<td><img src="media/empty.png" class="weblay_ico book_open"  title="book_open"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico database" title="database" ></img></td>
<td><img src="media/empty.png" class="weblay_ico monitor" title="monitor" ></img></td>
<td><img src="media/empty.png" class="weblay_ico iphone"  title="iphone"></img></td>
<td><img src="media/empty.png" class="weblay_ico ipad"  title="ipad"></img></td>
<td><img src="media/empty.png" class="weblay_ico battery_empty"  title="battery_empty"></img></td>
<td><img src="media/empty.png" class="weblay_ico battery_half"  title="battery_half"></img></td>
<td><img src="media/empty.png" class="weblay_ico battery_full"  title="battery_full"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico chart_pie" title="chart_pie" ></img></td>
<td><img src="media/empty.png" class="weblay_ico chart_bar" title="chart_bar" ></img></td>
<td><img src="media/empty.png" class="weblay_ico application"  title="application"></img></td>
<td><img src="media/empty.png" class="weblay_ico app_graph"  title="app_graph"></img></td>
<td><img src="media/empty.png" class="weblay_ico app_commandline"  title="app_commandline"></img></td>
<td><img src="media/empty.png" class="weblay_ico app_report"  title="app_report"></img></td>
<td><img src="media/empty.png" class="weblay_ico app_layout"  title="app_layout"></img></td>
<td><img src="media/empty.png" class="weblay_ico shell"  title="shell"></img></td>
</tr>


<tr>
<td><img src="media/empty.png" class="weblay_ico heart" title="heart" ></img></td>
<td><img src="media/empty.png" class="weblay_ico heart_solid" title="heart_solid" ></img></td>
<td><img src="media/empty.png" class="weblay_ico like"  title="like"></img></td>
<td><img src="media/empty.png" class="weblay_ico dislike"  title="like"></img></td>
<td><img src="media/empty.png" class="weblay_ico award"  title="award"></img></td>
<td><img src="media/empty.png" class="weblay_ico warning_ico"  title="warning_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico check"  title="check"></img></td>
<td><img src="media/empty.png" class="weblay_ico checkbox_checked"  title="checkbox_checked"></img></td>
<td><img src="media/empty.png" class="weblay_ico checkbox_disabled"  title="checkbox_disabled"></img></td>
<td><img src="media/empty.png" class="weblay_ico check_styled"  title="check_styled"></img></td>
<td><img src="media/empty.png" class="weblay_ico checkbox_round"  title="checkbox_round"></img></td>
<td><img src="media/empty.png" class="weblay_ico checkbox_round_disabled"  title="checkbox_round_disabled film"></img></td>
<td><img src="media/empty.png" class="weblay_ico checkbox_round_rev"  title="checkbox_round_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico star"  title="star"></img></td>
<td><img src="media/empty.png" class="weblay_ico star_bold"  title="star_bold"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico beaker" title="beaker" ></img></td>
<td><img src="media/empty.png" class="weblay_ico bomb" title="bomb" ></img></td>
<td><img src="media/empty.png" class="weblay_ico stamp"  title="stamp"></img></td>
<td><img src="media/empty.png" class="weblay_ico mug"  title="mug"></img></td>
<td><img src="media/empty.png" class="weblay_ico lightbulb"  title="lightbulb"></img></td>
<td><img src="media/empty.png" class="weblay_ico suitcase"  title="suitcase"></img></td>
<td><img src="media/empty.png" class="weblay_ico funnel"  title="funnel"></img></td>
<td><img src="media/empty.png" class="weblay_ico ticket"  title="ticket"></img></td>
<td><img src="media/empty.png" class="weblay_ico bug"  title="bug"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico text" title="text" ></img></td>
<td><img src="media/empty.png" class="weblay_ico underline_ico" title="underline_ico" ></img></td>
<td><img src="media/empty.png" class="weblay_ico italic_ico"  title="italic_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico bold_ico"  title="bold_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico strikethrough_ico"  title="strikethrough_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico justify_ico"  title="justify_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico left_ico"  title="left_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico center_ico"  title="center_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico right_ico"  title="right_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico ul_list_ico"  title="ul_list_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico ol_list_ico"  title="ol_list_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico outdent_ico"  title="outdent_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico indent_ico"  title="indent_ico"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico page_plus" title="page_plus" ></img></td>
<td><img src="media/empty.png" class="weblay_ico save_ico" title="save_ico" ></img></td>
<td><img src="media/empty.png" class="weblay_ico cut_ico"  title="cut_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico paste_ico"  title="paste_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico copy_ico"  title="copy_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico undo_ico"  title="undo_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico redo_ico"  title="redo_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico search_ico"  title="search_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico print_ico"  title="print_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico anchor"  title="anchor"></img></td>
<td><img src="media/empty.png" class="weblay_ico erase_ico"  title="erase_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico markup"  title="markup"></img></td>
<td><img src="media/empty.png" class="weblay_ico quote_ico"  title="quote_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico invisibles_ico"  title="invisibles_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico stroke_color_ico"  title="stroke_color_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico text_color_ico"  title="text_color_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico hr_ico"  title="hr_ico"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico arrow_right_sq_rev" title="arrow_right_sq_rev" ></img></td>
<td><img src="media/empty.png" class="weblay_ico arrow_right_round_rev" title="arrow_right_round_rev" ></img></td>
<td><img src="media/empty.png" class="weblay_ico arrow_right"  title="arrow_right"></img></td>
<td><img src="media/empty.png" class="weblay_ico arrow_right_dashed"  title="arrow_right_dashed"></img></td>
<td><img src="media/empty.png" class="weblay_ico play_round_rev"  title="play_round_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico gt_arrow"  title="gt_arrow"></img></td>
<td><img src="media/empty.png" class="weblay_ico arrow_right_round_sm"  title="arrow_right_round_sm"></img></td>
<td><img src="media/empty.png" class="weblay_ico play_arrow_sm"  title="play_arrow_sm"></img></td>
<td><img src="media/empty.png" class="weblay_ico right_arrow_fat_sm"  title="right_arrow_fat_sm"></img></td>
<td><img src="media/empty.png" class="weblay_ico right_arrow_dashed_bold"  title="right_arrow_dashed_bold"></img></td>
<td><img src="media/empty.png" class="weblay_ico gt_arrow_sm"  title="gt_arrow_sm"></img></td>
<td><img src="media/empty.png" class="weblay_ico down_arrow_sm"  title="down_arrow_sm"></img></td>
<td><img src="media/empty.png" class="weblay_ico down_arrow_rev_sm"  title="down_arrow_rev_sm"></img></td>
<td><img src="media/empty.png" class="weblay_ico arrow_down_right"  title="arrow_down_right"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico sound_mute" title="sound_mute" ></img></td>
<td><img src="media/empty.png" class="weblay_ico sound_min" title="sound_min" ></img></td>
<td><img src="media/empty.png" class="weblay_ico sound_max"  title="sound_max"></img></td>
<td><img src="media/empty.png" class="weblay_ico last_track"  title="last_track"></img></td>
<td><img src="media/empty.png" class="weblay_ico rewind"  title="rewind"></img></td>
<td><img src="media/empty.png" class="weblay_ico pause"  title="pause"></img></td>
<td><img src="media/empty.png" class="weblay_ico play"  title="play"></img></td>
<td><img src="media/empty.png" class="weblay_ico fastfwd"  title="fastfwd"></img></td>
<td><img src="media/empty.png" class="weblay_ico next_track"  title="next_track"></img></td>
<td><img src="media/empty.png" class="weblay_ico last_track_rev"  title="last_track_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico rewind_rev"  title="rewind_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico pause_rev"  title="pause_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico fastfwd_rev"  title="fastfwd_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico next_track_rev"  title="next_track_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico headphones"  title="headphones"></img></td>
<td><img src="media/empty.png" class="weblay_ico mic"  title="mic"></img></td>
<td><img src="media/empty.png" class="weblay_ico hr_ico"  title="book_open"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico refresh_double" title="refresh_double" ></img></td>
<td><img src="media/empty.png" class="weblay_ico refresh_single" title="refresh_single" ></img></td>
<td><img src="media/empty.png" class="weblay_ico refresh_single_sm"  title="refresh_single_sm"></img></td>
<td><img src="media/empty.png" class="weblay_ico refresh_double_med"  title="refresh_double_med"></img></td>
<td><img src="media/empty.png" class="weblay_ico question_ico"  title="question_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico exclamation_ico"  title="exclamation_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico info_ico"  title="info_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico question_ico_rev"  title="question_ico_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico exclamation_ico_rev"  title="exclamation_ico_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico info_ico_rev"  title="info_ico_rev"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico x_round" title="x_round" ></img></td>
<td><img src="media/empty.png" class="weblay_ico minus_round" title="minus_round" ></img></td>
<td><img src="media/empty.png" class="weblay_ico plus_round"  title="plus_round"></img></td>
<td><img src="media/empty.png" class="weblay_ico minus_round_rev"  title="minus_round_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico plus_round_rev"  title="plus_round_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico minus_sq_rev"  title="minus_sq_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico plus_sq_rev"  title="plus_sq_rev"></img></td>
<td><img src="media/empty.png" class="weblay_ico plus_sq_rev_sm"  title="plus_sq_rev_sm"></img></td>
<td><img src="media/empty.png" class="weblay_ico plus_bold"  title="plus_bold"></img></td>
<td><img src="media/empty.png" class="weblay_ico minus_bold"  title="minus_bold"></img></td>
<td><img src="media/empty.png" class="weblay_ico x_bold"  title="x_bold"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico power_ico" title="power_ico" ></img></td>
<td><img src="media/empty.png" class="weblay_ico sendto_ico"  title="sendto_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico border_adj_cursor"  title="border_adj_cursor"></img></td>
<td><img src="media/empty.png" class="weblay_ico move_cursor"  title="move_cursor"></img></td>
<td><img src="media/empty.png" class="weblay_ico fullscreen_ico"  title="fullscreen_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico exit_fullscreen_ico "  title="exit_fullscreen_ico "></img></td>
<td><img src="media/empty.png" class="weblay_ico resize_window_ico"  title="resize_window_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico resize_horizontal"  title="resize_horizontal"></img></td>
<td><img src="media/empty.png" class="weblay_ico sendto2_ico"  title="sendto2_ico"></img></td>
<td><img src="media/empty.png" class="weblay_ico sendto_arrow"  title="sendto_arrow"></img></td>
<td><img src="media/empty.png" class="weblay_ico send_recieve"  title="send_recieve"></img></td>
<td><img src="media/empty.png" class="weblay_ico download_varrow"  title="download_varrow"></img></td>
<td><img src="media/empty.png" class="weblay_ico download_tri_arrow"  title="download_tri_arrow"></img></td>
<td><img src="media/empty.png" class="weblay_ico ret_button_left"  title="ret_button_left"></img></td>
<td><img src="media/empty.png" class="weblay_ico ret_button_up"  title="ret_button_up"></img></td>
<td><img src="media/empty.png" class="weblay_ico ret_button_down"  title="ret_button_down"></img></td>
</tr>

<tr>
<td><img src="media/empty.png" class="weblay_ico icon_view" title="icon_view" ></img></td>
<td><img src="media/empty.png" class="weblay_ico tile_view" title="tile_view" ></img></td>
<td><img src="media/empty.png" class="weblay_ico listing_view"  title="listing_view"></img></td>
<td><img src="media/empty.png" class="weblay_ico details_view"  title="details_view"></img></td>
</tr>

</table>


</body></html>
