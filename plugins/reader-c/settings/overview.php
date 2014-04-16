<style>
.reader-map .node rect {
  /*cursor: move;*/
  fill-opacity: .8;
  shape-rendering: crispEdges;
}
 
.reader-map .node text {
  pointer-events: none;
  text-shadow: 0 1px 0 #fff;
  font-size:11px;
  font-family: "Open Sans", Helvetica, Arial, "Nimbus Sans L", sans-serif;
}
.reader-map .node text.hide {
  display:none;
}
 
.reader-map .link {
  fill: none;
  stroke: #000;
  stroke-opacity: .2;
}
 
.reader-map .link:hover {
  stroke-opacity: .5;
}
</style>
<div id="reader_c_overview" class="wrap"> 
	<div id="content" class="reader-map"><h2>ReaderC Summary</h2><?php echo do_shortcode('[reader_c_map]'); ?></div>
	<!-- <div class="reader_c_header" style="text-align:center"><a href="http://oerresearchhub.org/" title="OER Research Hub" rel="home"> <img src="http://oerresearchhub.files.wordpress.com/2013/07/cropped-oer_700-banner-2.jpg" width="513" alt=""> </a></div> -->    
</div>