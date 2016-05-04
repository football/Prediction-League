<?xml version="1.0" encoding="UTF-8"?>
<!-- phpBB Extension - Football Football - seasons-data.prosilver.xsl v0.9.4 
	@copyright (c) 2016 football (http://football.bplaced.net)
	@license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2. -->
<!DOCTYPE xsl:stylesheet[
	<!ENTITY nbsp "&#160;">
]>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="2.0" xmlns:seasons-data="http://football.bplaced.net/ext/football/football/xml/seasons-data-0.9.4.xsd">
	<xsl:output method="html" omit-xml-declaration="no" indent="yes" />
	<xsl:variable name="code" select="seasons-data:seasons-data/seasons-data:code" />

	<xsl:template match="seasons-data:seasons-data">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Language" content="de" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
/*  phpBB 3.0 Admin Style Sheet
	–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
	Original author:	subBlue ( http://www.subblue.com/ )
	Copyright 2007 phpBB Group ( http://www.phpbb.com/ )
	–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
*/

/* General markup styles
––––––––––––––––––––––––––––––*/
* {
	/* Reset browsers default margin, padding and font sizes */
	margin:0;
	padding:0;
	font-size:100%;
}

/*.rtl * {
	text-align:right;
	direction: rtl;
}*/

body, div, p, th, td, li, dd {
	font-size:x-small;
	voice-family:"\"}\"";
	voice-family:inherit;
	font-size:100%;
}

html>body, html>div, html>p, html>th, html>td, html>li, html>dd {
	font-size:small
}

html {
	color:#536482;
	background:#DBD7D1;
	/* Always show a scrollbar for short pages - stops the jump when the scrollbar appears. non-ie browsers */
	height:100%;
	margin-bottom:1px;
}

body {
	/* Text-Sizing with ems:http://www.clagnut.com/blog/348/ */
	font-family:"Lucida Grande", Verdana, Helvetica, Arial, sans-serif;
	color:#536482;
	background:#DBD7D1;
	font-size:82.5%;	/* This sets the default font size to be equivalent to 10px */
	margin:10px 15px;
}

img {
	border:0;
}

h1 {
	font-family:"Trebuchet MS", Helvetica, sans-serif;
	font-size:1.70em;
	font-weight:normal;
	color:#333333;
}

h2, caption {
	font-family:"Trebuchet MS", Helvetica, sans-serif;
	font-size:1.40em;
	font-weight:normal;
	color:#115098;
	text-align:left;
	margin-top:25px;
}

.rtl h2, .rtl caption {
	text-align:right;
}

h3, h4, h5 {
	font-family:"Trebuchet MS", Helvetica, sans-serif;
	font-size:1.20em;
	text-decoration:none;
	line-height:1.20em;
	margin-top:10px;
}

p {
	margin-bottom:0.7em;
	line-height:1.40em;
	font-size:1.0em;
}

ul {
	list-style:disc;
	margin:0 0 1em 2em;
}

.rtl ul {
	margin:0 2em 1em 0;
}

hr {
	border:0 none;
	border-top:1px dashed #999999;
	margin-bottom:5px;
	padding-bottom:5px;
	height:1px;
}

.small {
	font-size:0.85em;
}

/* General links  */
a:link, a:visited {
	color:#105289;
	text-decoration:none;
}

a:link:hover {
	color:#BC2A4D;
	text-decoration:underline;
}

a:active {
	color:#368AD2;
	text-decoration:none;
}

/* Main blocks
––––––––––––––––––––––––––––––––––––––––*/
#wrap {
	padding:0 0 15px 0;
	min-width:615px;
}

#page-header {
	clear:both;
	text-align:right;
	font-size:0.85em;
	margin-bottom:10px;
}

.rtl #page-header {
	text-align:left;
	background:top right no-repeat;
}

#page-header h1 {
	color:#767676;
	font-family:"Trebuchet MS",Helvetica,sans-serif;
	font-size:1.70em;
	padding-top:10px;
}

#page-header p {
	font-size:1.00em;
}

#page-body {
	clear:both;
	min-width:700px;
}

#page-footer {
	clear:both;
	font-size:0.75em;
	text-align:center;
}

#content {
	padding:0 10px 10px 10px;
	position:relative;
}

#content h1 {
	color:#115098;
	line-height:1.2em;
	margin-bottom:0;
}

#footb {
	background:url(data:image/gif;base64,R0lGODlheAB4ALMAAAAAAEYAAP8IEP8QGPLIev///wUAA////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAAcALAAAAAB4AHgAQwT/8MhJq7046827/2AojmRpSkAKBKyaEsApz3Tl3jih13w/4oBCwTUE6Fy+pPIATAmfUOhyWsM9mURiFBqjej0vHQysKpa337QG2BEM3nCBuQCvC9T4mWDP5wudYi5ig4EpX0FcWk+GU00qHECDTkU9in+PlUBFZjczjmeYWCg4J5OfiIx5JKSZp65IE6+ys5+qtre4ubozLAGhu8AWKi1NYsHHwo4wXci7VltRKoSFYZ3NJWdbn9HXXtW/FM/Ql91KL6wZjlF95bp1b32b732E4HjZifatAHsDtNLLBI1JgmiRpSFpTolAVQuYkyactiG09Y/Zj4ip2mncyLGjx48g/0OKHHkvBTFiFkkuMYnSl0CVU1zKgunt0xGaVF4tw7nv4aR8RurluMnzwsFxQME5KorlZzSkV5iGwGfwBlRKUiGF2RmOKjRDKbPGkhQ2WRCnX8Umo1bWxlE0arta47BHEaI+fOKOaovh3QA/Tvzi1Yer4CW+evD6mUNvGtk8b7mZU9ynWEDHhRpps2qmkcJYNtly3fd0UcKGH875nMuD9UqfGGH9eOUwo1lNq2VXeVbRE0PeS5t99lSVjd4Th48rJ1PR+PLn0KNLn069uvXr2LMXbtpCJmLtEnqxHGbkO/bxlsEbdWVMfVeUkQa636tzvlybhMHTtn87h+35rgxxhP9506kTjUABhUUgTpxBtRVm0+QHU4NXNacbT5pcFVUHmCzoEYUabmidV1Uh5aFYJHISUVrUUSjROP8dBxFoP6WI1XIABdRfhRIWFdSAfGVhGIsy1sPhalDhKAlzNQ4pBI7lPZaOOFvsoVwTWkEkhGJ6OaeBG3WUEQSY73R5YQZkxiGHPPOw042XPQgGGB1t7gHkieY46RoNlO1xSQGUQYjnbnpK1kOfa54BYYSDYrMIF9osgehhW+XoWI8y6GlJjDSk0McbOuX4IFE+aIoWp759xp5o8pHmYE7D3RefJE+6+ig5NQXHZCGclGrYmbBiOVWT6NB0VgFNwZmaKyRxdpZTrrPtl2WjqVqVG7CrqNMboaE8ewql1C702yy7gWvKK8jm8i22s5mrxZ7CWVFqcdCChOlFsYUbHaX8TdtctBbqe0jABBf8b78IJ6zwwgw37LAHEQAAOw==) top left no-repeat;
	height:120px;
}

#main {
	width:100%;
	margin:0;
	min-height:350px;
}

.rtl #main {
	float:right;
	margin:0;
}

* html #main {
	height:350px;
}

/* Main Panel
–––––––––––––––––––––––––––––––––––––––– */
#acp {
	margin:4px 0;
	padding:3px 1px;
	min-width:550px;
	background-color:#FFFFFF;
	border:1px #999999 solid;
}

.panel {
	background:#F3F3F3 url(data:image/gif;base64,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) repeat-x top;
	padding:0;
}

span.corners-top, span.corners-bottom, span.corners-top span, span.corners-bottom span {
	font-size:1px;
	line-height:1px;
	display:block;
	height:5px;
	background-repeat:no-repeat;
}

span.corners-top, span.corners-bottom {
	background-image:url(data:image/gif;base64,R0lGODlhiBMMAMQSAP///5mZmfPz89vX0cvIw9zc3PX19bKysqmopvb29pqamvn5+Z2dnLy6t62sqp+fnqCgn/T09P///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABIALAAAAACIEwwAAAX/4DAQDhScaKqubOu+cCzPdG3feK7vfO//wKBwSCwaj8ikcslsOp/QqHRKrVqv2Kx2y+16v+CweEwum8/otHrNbrvf8Lh8Tq/b7/i8fs/v+/+AgYKDhIWGh4hSAw0HBREAkJGSk5SVlpeYmZqbnJ2en6ChoqOkpaanqKmqq6ytrq+wsbKztLW2t7i5uru8vb6/wMHCw8TFxsfIycrLzM3Oz9DR0tPU1dbX2Nna29zd3t/g4eLj5OXm5+jp6uvs7e7v8PG7BAoCAAkCEvr7/P3+/wADChxIsKDBgwgTKlzIsKHDhxAjSpxIsaLFixgzatzIsaPHjyBDihxJsqTJkyhT/6pcybKly5cwY8qcSbOmzZs4c+rcybOnz59AgwodSrSo0aNIkypdyrSp06dQo0qdSrWq1atYs2rdShJBAQBcw4odS7as2bNo06pdy7at27dw48qdS7eu3bt48+rdy7ev37+AAwseTLiw4cOIizJYYCCx48eQI0ueTLmy5cuYM2vezLmz58+gQ4seTbq06dOoU6teHToAgHysY8ueTbu27du4c+vezbu379/AgwsfTry48ePIkyuX4Br28ufQo0ufTr269evYs2vfzr279+/gw4sfT7704sbl06tfz769+/fw48ufT7++/fv48+vfzx+vV7D9BSjggAQWaOCBCCao4JmCDDbo4IMQRijhdfTYg8+EGGao4YYcdujhhyCGKOKIJJZo4oko5rVIIwbI4+KLMMYo44w01mjjjTjmqOOOPPbo449ABinkkEQWaeSRSCap5JJMNunkk1BGKeWUVFaZjQgkPJDIllx26eWXYIYp5phklmnmmWimqeaabLbp5ptwxinnnHTWaeedeOap55589unnn4AGKigcIQAAOw==);
}

span.corners-top span, span.corners-bottom span {
	background-image:url(data:image/gif;base64,R0lGODlhBgAMAMQWAP///9vX0fPz85mZmdzc3Jqamtvb28vIw7y6t62sqsvIxPX19bKysqmop/T09Pb29pycnJ+fnp2dnKCgn/n5+fr6+v///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABYALAAAAAAGAAwAAAUs4DAlRxAADsEggfAAQqFYNGA0tLVQUi4AAx8w56hAcraGCyYDLFSsQYRkCgEAOw==);
}

span.corners-top {
	background-position:0 0;
	margin:-4px -2px 0;
}

span.corners-top span {
	background-position:100% 0;
}

span.corners-bottom {
	background-position:0 100%;
	margin:0 -2px -4px;
	clear:both;
}

span.corners-bottom span {
	background-position:100% 100%;
}

span.leagueid { font-size:12px; line-height:14px; padding-bottom:2px; width:40px; border:outset 2px #999999; background-color:#EEEECC; display:block; float:left; text-align:center; margin-right:5px; }

/* General form styles
––––––––––––––––––––––––––––––––––––––––*/
fieldset {
	margin:25px 0;
	padding:1px 0;
	border-top:1px solid #D7D7D7;
	border-right:1px solid #CCCCCC;
	border-bottom:1px solid #CCCCCC;
	border-left:1px solid #D7D7D7;
	background-color:#FFFFFF;
	position:relative;
}

.rtl fieldset {
	border-top:1px solid #D7D7D7;
	border-right:1px solid #D7D7D7;
	border-bottom:1px solid #CCCCCC;
	border-left:1px solid #CCCCCC;
}

* html fieldset {
	padding:0 10px 5px 10px;
}

fieldset p {
	font-size:1.0em;
}

legend {
	padding:1px 5px;
	font-family:Tahoma,arial,Verdana,Sans-serif;
	font-size:1.06em;
	font-weight:bold;
	color:#115098;
	margin-top:-.4em;
	position:relative;
/*	text-transform:capitalize;*/
	line-height:1.00em;
	top:0;
	vertical-align:middle;
}

/* Hide from macIE \*/
legend { top:-1.2em; }
/* end */

* html legend {
	margin-bottom:-10px;
	margin-left:-7px;
}

/* Holly hack, .rtl comes after html */
* html .rtl legend {
	margin:0;
	margin-right:-7px;
}

optgroup, select {
	font-family: Verdana, Helvetica, Arial, sans-serif;
	font-size: 0.85em;
	font-weight: normal;
	font-style: normal;
	cursor: pointer;
	vertical-align: middle;
	width: auto;
}

optgroup {
	font-size: 1.00em;
	font-weight: bold;
}

option {
	padding:0 1em 0 0;
}

.rtl option {
	padding:0 0 0 1em;
}

fieldset.nobg {
	margin:15px 0 0 0;
	padding:0;
	border:none;
	background-color:transparent;
}

/* SEASON-ABOUT STUFFS ~smithy_dll */

.footb-block {
	background-color:#CADCEB;
	/*width:100%;*/
}

.footb-block span.corners-top, .footb-block span.corners-bottom, .footb-block span.corners-top span, .footb-block span.corners-bottom span {
	font-size:1px;
	line-height:1px;
	display:block;
	height:5px;
	background-repeat:no-repeat;
}

.footb-block span.corners-top, .footb-block span.corners-bottom {
background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAYAAAAMCAYAAABBV8wuAAAABGdBTUEAANbY1E9YMgAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAABVSURBVHjaYviPAD+BeDIQ2wMxGwNU8AkQGwAxAwwzQFXqIgvCJCajC8IkbLBJMIItYmD4xYAGmBhwAJCEMS6JcKxa8DkX5kFdbBKwIJkADRIGgAADAGtyotIvyqGpAAAAAElFTkSuQmCC);
}

.footb-block span.corners-top span, .footb-block span.corners-bottom span {	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAYAAAAMCAYAAABBV8wuAAAABGdBTUEAANbY1E9YMgAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAABbSURBVHjaYvr//z8bENsD8WQg/vkfChjQsAEQPwFJMDGgggtA7AnEv9AlQOAyEM/CJgECK3FJnMIlwYZLQheXRDg2CV0gzmTCIrgd2Q4bIJ4AxGeAWBokABBgAE4XMoXm9S+UAAAAAElFTkSuQmCC);
}

.footb-block span.corners-top {
	background-position:0 0;
	margin:0 0;
}

.footb-block span.corners-top span {
	background-position:100% 0;
}

.footb-block span.corners-bottom {
	background-position:0 100%;
	margin:0 0;
	clear:both;
}

.footb-block span.corners-bottom span {
	background-position:100% 100%;
}

.footb-block-padding { padding: 0 8px; }
.footb-block { margin:7px 4px 10px 4px; }
.footb-block dt { font-weight:bold; padding-right:4px; }
.rtl .footb-block dt { padding-left: 4px; }
.footb-block dl { margin:0 8px; }
.footb-block div { margin:3px 8px;}
/*div.inner .footb-block dl { margin:0; }*/
/*.nopadding { margin:0; }*/

#language { width:130px; }

dt {
	float: left;
	width:auto;
}

.rtl dt {
	float: right !important;
	text-align: right;
	width:auto;
}

dd { color:#666666; }
dd + dd { padding-top: 5px; }

dt span { padding: 0 5px 0 0; }
.rtl dt span { padding: 0 0 0 5px; }

		</style>
		<title>phpBB3 Football Extension &#187; Spielpläne dieser Seite</title>
		<script type="text/javascript">
			var i = 0;

			/* passed from xslt */
			var seasons_ll = [];
			var league_ll = [];
			<xsl:for-each select="seasons-data:season">
				seasons_ll.push('<xsl:value-of select="generate-id()"/>');
					<xsl:for-each select="seasons-data:league">
						league_ll.push('<xsl:value-of select="generate-id()"/>');
					</xsl:for-each>
			</xsl:for-each>


			<xsl:text disable-output-escaping="yes">
<![CDATA[
var host = "http://football.bplaced.net/ext/football/football/xml/";

var deStrings = "dir=ltr\n" +
"foot=Copyright &#169; 2011 phpBB3 Football Extension.\n" +
"s_title=Spielpläne dieser Seite\n" +
"l_title=Spielplan\n" +
"h1l=Der Spielplan\n" +
"h1s=Auf dieser Seite zum Download angebotene Spielpläne\n" +
"h3-pointswins=Punkte und Gewinne\n" +
"link-l=Sprache\n" +
"l-l=Liga\n" +
"nls=Keine Spielpläne vorhanden\n" +
"nll=Keine Spielplandaten vorhanden\n" +
"s-fixl=Spielpläne der Saison\n" +
"s-season=Saison\n" +
"slg=Sprache auswählen:\n" +
"t-leagues=Ligadaten\n" +
"t-matchdays=Spieltage\n" +
"t-matches=Spielpaarungen\n" +
"t-seasons=Saisondaten\n" +
"t-teams=Mannschaften\n" +
"tf-match_datetime=Datum\n" +
"tf-match_no=SpNr\n" +
"tf-match_matchday=SpTag\n" +
"tf-match=Begegnung\n" +
"tf-result=Ergebnis\n" +
"tf-extratime=Verl.\n" +
"tf-match_status=Status\n" +
"tf-group=Gruppe\n" +
"tf-ko=KO\n" +
"tf-formula=Formel\n" +
"tf-team_id=Team-ID\n" +
"tf-team_name=Mannschaftsbezeichnung\n" +
"tf-team_name_short=Kurzbezeichnung\n" +
"tf-team_symbol=Wappen\n" +
"tf-group_id=Gruppe\n" +
"tf-participate=Teiln. bis Spieltag\n" +
"tf-league=Saison\n" +
"tf-league_name=Liga Bezeichnung\n" +
"tf-league_name_short=Liga Kürzel\n" +
"tf-league_type=Typ\n" +
"tf-matchdays=Spieltage\n" +
"tf-matches_on_matchday=Spiele/Sptag\n" +
"tf-win_result=G. Volltreffer\n" +
"tf-win_result_02=G. Volltreffer 02\n" +
"tf-win_matchday=G. Spieltag\n" +
"tf-win_season=G. Saison\n" +
"tf-points_mode=P. Modus\n" +
"tf-points_result=P. Volltreffer\n" +
"tf-points_tendency=P. Tendenz\n" +
"tf-points_diff=P. Differenz\n" +
"tf-points_last=P. Nichttipper\n" +
"tf-join_by_user=User Beitritt\n" +
"tf-join_in_season=Beitritt in Saison\n" +
"tf-bet_in_time=Tippen bis Spielbeginn\n" +
"tf-rules_post_id=Regeln\n" +
"tf-bet_ko_type=Tippart\n" +
"tf-bet_points=Einsatz\n" +
"tf-matchday=Spieltag\n" +
"tf-status=Status\n" +
"tf-delivery_date=1. Abgabetermin\n" +
"tf-delivery_date_2=2. Abgabetermin\n" +
"tf-delivery_date_3=3. Abgabetermin\n" +
"tf-matchday_name=Spieltagsbezeichnung\n" +
"tf-matches=Anzahl Spiele\n" +
"tf-season=Saison\n" +
"tf-season_name=Saison Bezeichnung\n" +
"tf-season_name_short=Saison Kurzbezeichnung\n" +
"tnl=Keine Ligadaten vorhanden\n" +
"tnmd=Keine Spieltagsdaten vorhanden\n" +
"tnm=Keine Spiele vorhanden\n" +
"tns=Keine Saisondaten vorhanden\n" +
"tnt=Keine Mannschaftsdaten vorhanden";


var currentLanguage = "de";
var languagesLoaded = false;
var languages = ['de'];
var arrClasCnt = [
	['s-'	, seasons_ll	],
	['l-'	, league_ll		]
];

function startup()
{
	changeLanguage(currentLanguage);
	document.getElementById('lang-selector').style.display = "block";
}

function changeLanguage(langCode)
{
	langCode = langCode.toLowerCase();
	currentLanguage = langCode.split('-')[0];
	if (currentLanguage.toLowerCase() != 'de') // if change, only include up to first dash
	{
		load_language();
	}
	else
	{
		applyLanguage(deStrings.split("\n"));
	}
}

function load_languages()
{
	if (languagesLoaded)
	{
		return;
	}
	languagesLoaded = true;

	$divname = document.getElementById('language');
	var loadingItem = document.createElement('option');
	$divname.appendChild(loadingItem);
	loadingItem.innerHTML = 'Loading...';
	$divname.remove(0);
	$output = 'load_languages';
	cachernd = parseInt(Math.random() * 99999999); // cache
	send('', host + 'languages.txt?rnd=' + cachernd);
}

function load_language()
{
	$output = 'load_language';
	cachernd = parseInt(Math.random() * 99999999); // cache
	send('', host + currentLanguage + '.txt?rnd=' + cachernd);
}

/*****************
* AJAX Functions *
*****************/
var $xmlhttp = http_object();
var $finished = 0;
var $send_queue = [];
var $running = false;
var $divname;
var $newform;
var $newurl;
var $output;

function http_object()
{
	if (window.XMLHttpRequest)
	{
		return new XMLHttpRequest();
	}
	else if (window.ActiveXObject)
	{
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
}

function send($action, $url, $form, $div, $clear)
{
	$newform = $form;
	$newurl = $url;

	$send_queue.push("handle_send($newurl, $newform)");

	if (!$running)
	{
		run_ajax();
	}
	return;
}

function run_ajax()
{
	$running = true;
	for ($i = 0; $i < $send_queue.length; $i++)
	{
		if ($xmlhttp.readyState == 4 || $xmlhttp.readyState == 0)
		{
			eval($send_queue[$i]);
		}
		else
		{
			$xmlhttp.onreadystatechange = check_state;
		}
	}
}

function check_state()
{
	if ($xmlhttp.readyState == 4 || $xmlhtt.readyState == 0)
	{
		eval($send_queue[$finished]);
	}
	else
	{
		$xmlhttp.onreadystatechange = check_state;
	}
}

function handle_send($url, $f)
{
	if ($xmlhttp.readyState == 4 || $xmlhttp.readyState == 0)
	{
		$param = '';

		try
		{
			netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
			$allowed = true;
		}
		catch (e)
		{}

		try
		{
			$xmlhttp.open('POST', $url, true);
			$xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			$xmlhttp.onreadystatechange = handle_return;
			$xmlhttp.send($param);
		}
		catch (e)
		{
			$divname = document.getElementById('language');
			var loadingItem = document.createElement('option');
			$divname.appendChild(loadingItem);
			loadingItem.innerHTML = 'Unavailable';
			$divname.remove(0);
			$divname.disabled = true;
		}
	}
	return;
}

function handle_return()
{
	if ($xmlhttp.readyState == 4)
	{
		ajax_output($xmlhttp.responseText);

		$finished++;

		if ($send_queue[$finished])
		{
			check_state();
		}
		else
		{
			$send_queue = [];
			$finished = 0;
			$running = false;
		}
	}
	return;
}
/*********************
* END AJAX Functions *
*********************/

function ajax_output($response)
{
	switch ($output)
	{
		case 'load_language':
			var texts = $response.replace("\r\n", "\n").split("\n");
			applyLanguage(texts);
		break;

		case 'load_languages':
			languages = $response.replace("\r", "").split("\n");

			var i, l, s = 0;
			for (i in languages)
			{
				languages[i] = languages[i].replace("\r", "");
				languages[i] = languages[i].split("=");

				var langItem = document.createElement('option');
				$divname.appendChild(langItem);
				langItem.value = languages[i][0];
				var iso = languages[i][0].split('-');
				langItem.innerHTML = languages[i][1];
				if (iso.length == 2)
				{
					langItem.innerHTML += ' [' + iso[1].toUpperCase() + ']';
				}
				if (languages[i][0] == currentLanguage)
				{
					$divname.selectedIndex = s;
					$divname.text = languages[i][1];
				}
				s++;
			}

			$divname.focus();
			$divname.onchange =
			function()
			{
				changeLanguage(this.value.replace(" ", ""));
			};
			$divname.remove(0);
		break;
	}
}

function applyLanguage(texts)
{
	var i;

	for (i in texts)
	{
		var lang = texts[i].split("=");
		if(lang[0] == 'dir')
		{
			set_dir(lang[1]);
		}
		if (lang.length < 2)
		{
			continue;
		}
		try
		{
			var jflag = false;
			for (var j = 0; j < arrClasCnt.length; j++)
			{
				var sw = '-' + lang[0];
				if (sw.match('-' + arrClasCnt[j][0]))
				{
					for (var k = 0; k < arrClasCnt[j][1].length; k++)
					{
						try
						{
							var o = document.getElementById('lang-' + lang[0] + '[' + arrClasCnt[j][1][k] + ']');
							o.innerHTML = lang[1];
						}
						catch (e){}
					}
					jflag = true;
				}
			}
			if (!jflag)
			{
				var append = '';
				for (var p = 1; p < lang.length; p++)
				{
					append += (p > 1 ? '=' : '') + lang[p];
				}
				document.getElementById('lang-' + lang[0]).innerHTML = append;
			}
		}
		catch (o){}
	}
}

function set_dir(direction)
{
	direction = (direction == 'rtl') ? 'rtl' : 'ltr';
	document.body.style.direction=direction;

	var ie = /*@cc_on!@*/false;
	var dts = document.getElementsByTagName('dt');
	var uls = document.getElementsByTagName('ul');
	var h2s = document.getElementsByTagName('h2');

	var rtl_float = (ie) ? 'styleFloat' : 'cssFloat';

	if(direction == 'rtl')
	{
		for(j = 0; j < dts.length; j++)
		{
			if(ie)
			{
				dts[j].style.styleFloat='right';
			}
			else
			{
				dts[j].style.cssFloat='right';
			}
		}
		for(j = 0; j < h2s.length; j++)
		{
			h2s[j].style.textAlign='right';
		}
		for(j = 0; j < uls.length; j++)
		{
			uls[j].style.margin='0 2em 1em 0';
		}
	}
	else
	{
		for(j = 0; j < dts.length; j++)
		{
			if(ie)
			{
				dts[j].style.styleFloat='left';
			}
			else
			{
				dts[j].style.cssFloat='left';
			}
		}
		for(j = 0; j < h2s.length; j++)
		{
			h2s[j].style.textAlign='left';
		}
		for(j = 0; j < uls.length; j++)
		{
			uls[j].style.margin='0 0 1em 2em';
		}
	}
}

						//-->]]>
					</xsl:text>

				</script>
		</head>
		<body onload="startup()">
		<div id="debug"></div>
		<div id="wrap">
			<div id="page-header">
				<form method="post" action="" id="lang-selector" style="display: none;">
				<fieldset class="nobg">
					<label for="language"><span id="lang-slg">Sprache auswählen:</span></label>&nbsp;<select id="language" name="language" onclick="load_languages()"><option value="de" selected="selected">Deutsch</option></select>
				</fieldset>
				</form>
			</div>
			<div id="page-body">
				<div id="acp">
					<div class="panel"><span class="corners-top"><span></span></span>
						<div id="content">
						<h1><div id="footb"></div><span id="lang-h1s">Auf dieser Seite zum Download angebotene Spielpläne</span> </h1>
							<div id="main">
								<xsl:if test="count(seasons-data:season) > 0">
									<xsl:for-each select="seasons-data:season">
										<xsl:call-template name="give-season"></xsl:call-template>
									</xsl:for-each>
								</xsl:if>
								<xsl:if test="count(seasons-data:season) = 0">
									<span id="lang-nls">Keine Spielpläne vorhanden</span><br />
								</xsl:if>
							</div>
						</div>
					<span class="corners-bottom"><span></span></span></div>
				</div>
			</div>
			<div id="page-footer">
				<p class="copyright" style="text-align: center; font-size: 10px;" id="lang-foot">Copyright &#169; 2011 phpBB3 Football Extension.</p>
			</div>
		</div>
		</body>
		</html>
	</xsl:template>

	<xsl:template name="give-season">
		<div class="footb-block">
			<span class="corners-top"><span></span></span>
				<div>
				<h1><span id="lang-s-season[{generate-id()}]">Saison</span>&nbsp;<xsl:value-of select="seasons-data:season_id" /></h1>
				<hr />
				<h3><span id="lang-s-fixl[{generate-id()}]">Spielpläne der Saison</span>&nbsp;<xsl:value-of select="seasons-data:season_name_short" />:</h3>
				</div>
				<xsl:for-each select="seasons-data:league">
					<div>
						<xsl:variable name="thisleague" select="seasons-data:league_id" />
						<span class="leagueid"><xsl:value-of select="$thisleague" /></span>
						<xsl:variable name="URL">
						   league.php?season=<xsl:value-of select="../seasons-data:season_id" />&amp;league=<xsl:value-of select="$thisleague" />&amp;code=<xsl:value-of select="$code" />
						</xsl:variable>
						<a href="{$URL}"><xsl:value-of select="seasons-data:league_name" /></a>
						<br clear="all" />
					</div>
				</xsl:for-each>
			<span class="corners-bottom"><span></span></span>
		</div>
	</xsl:template>
	
</xsl:stylesheet>