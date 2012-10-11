<?php
/* $Id: movie_annotations.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * 
 * Movie annotations:
 * Demonstrate the use of a movie annotation in PDF. 
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7.0.3
 * Required data: movie file
 */
/* This is where the data files are. Adjust as necessary */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Movie Annotations";


/* movie file to be referenced by the link */
$moviefile = "../input/ski.avi";


try {
    /*
     * Positions for the Movie annotation and for the Link annotation
     * that can be used to start the movie. Align the movie with the
     * Link annotations.
     */
    $link_width = 80; 
    $link_heigth = 40; 
    $link_separation = 20;
    
    $movie_width = 2 * $link_width + $link_separation;
    $movie_height = $movie_width * 2.0 / 3.0;
    $movie_llx = 150; 
    $movie_lly = 300;
    $movie_urx = $movie_llx + $movie_width; 
    $movie_ury = $movie_lly + $movie_height;

    $start_llx = $movie_llx; 
    $start_lly = $movie_lly - $link_separation - $link_heigth; 
    $start_urx = $start_llx + $link_width; 
    $start_ury = $start_lly + $link_heigth;
    $stop_llx = $movie_llx + $link_width + $link_separation; 
    $stop_lly = $start_lly;
    $stop_urx = $stop_llx + $link_width; 
    $stop_ury = $start_ury;

    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "bytes");

    $p->set_parameter("SearchPath", $searchpath);

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
	    . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
	    . $p->get_errmsg());

    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");

    /* Create a "Movie" action for playing the movie */
    $start_action = $p->create_action("Movie", "target=mymovie");
    $stop_action = $p->create_action("Movie", 
			    "target=mymovie operation=stop");
    
    /*
     * Option list for the Link annotation to play a movie.
     * "showcontrols" shows a controller bar while playing the movie.
     * "movieposter auto" displays a poster image retrieved from the
     * movie file. "playmode open" plays the movie once and leaves the
     * movie controller bar open.
     */
    $optlist = "name=mymovie linewidth=0 filename={" . $moviefile
	    . "} showcontrols movieposter=auto playmode=open";

    /*
     * Create a Movie annotation.
     */
    $p->create_annotation($movie_llx, $movie_lly, $movie_urx, $movie_ury, 
	    "Movie", $optlist);

    /*
     * Create Link annotations to start and stop the movie.
     */
    $play_movie = "Play";
    $stop_movie = "Stop";
    $options = "position=center boxsize={" . $link_width
	. " " . $link_heigth . "}";
    
    $p->setfont($font, 12);
    $p->fit_textline($play_movie, $start_llx, $start_lly, $options);
    $p->create_annotation($start_llx, $start_lly, $start_urx, $start_ury,
	    "Link", "linewidth=2 action={activate " . $start_action
		    . "} contents={" . $play_movie . "}");

    $p->fit_textline($stop_movie, $stop_llx, $stop_lly, $options);
    $p->create_annotation($stop_llx, $stop_lly, $stop_urx, $stop_ury,
	    "Link", "linewidth=2 action={activate " . $stop_action
		    . "} contents={" . $stop_movie . "}");
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=movie_annontations.pdf");
    print $buf;

    } catch (PDFlibException $e) {
        die("PDFlib exception occurred:\n".
            "[" . $e->get_errnum() . "] " . $e->get_apiname() .
            ": " . $e->get_errmsg() . "\n");
    } catch (Exception $e) {
        die($e->getMessage());
    }

$p=0;

?>
