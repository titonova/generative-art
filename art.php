<?php
error_reporting( E_ALL);
ini_set('show_errors',1);
define( 'GRID_WIDTH', 20 );
define( 'GRID_HEIGHT', 20 );
define( 'TL_SIZE', 16 );
define( 'ITEM_COUNT', 3 );
define( 'KEEP_EXISTING', false );
define( 'TL_DEFAULT', 0 );
define( 'TL_ROAD', 1 );
define( 'TL_PARK', 2 );
define( 'MULT', 2 );
define( 'WIDTH', GRID_WIDTH * TL_SIZE * MULT );
define( 'HEIGHT', GRID_HEIGHT * TL_SIZE * MULT );

$testGD = get_extension_funcs( 'gd' );
if ( ! $testGD ) {
	echo 'GD not installed.';
	die();
}
$world = array();
$tl_image = imagecreatefrompng( './tiles.png' );


generate_images();
show_images();

function generate_world() {

	global $world;
	$world = array();
	for ( $y = 0; $y < GRID_HEIGHT; $y ++ ) {
		$world[$y] = array_fill( 0, GRID_WIDTH, TL_DEFAULT );
	}
	$directions = array( 'ns', 'ew' );
	$grid = 4; // Maybe this could be a random size for each image?
	$road_count = ( GRID_WIDTH * GRID_HEIGHT ) * 0.03;
	for ( $c = 0; $c < $road_count; $c ++ ) {
		shuffle( $directions );
		$length = rand( 6, 12 );
		$startX = rand( 0, round( GRID_WIDTH / $grid ) ) * $grid;
		$startY = rand( 0, round( GRID_HEIGHT / $grid ) ) * $grid;
		$endX = $startX;
		$endY = $startY;
		if ( 'ns' === $directions[0] ) {
			$endY = min( $endY + $length, GRID_HEIGHT );
		}
		if ( 'ew' === $directions[0] ) {
			$endX = min( $endX + $length, GRID_WIDTH );
		}
		for ( $y = $startY; $y <= $endY; $y ++ ) {
			for ( $x = $startX; $x <= $endX; $x ++ ) {
				$world[$y][$x] = TL_ROAD;
			}
		}

	}
	$park_count = ( GRID_WIDTH * GRID_HEIGHT ) * 0.03;	// Tweak the numbers until they feel good.
	$grid = rand( 2, 4 );

	for( $c = 0; $c < $park_count; $c++ ) {
		$startX = rand( 0, round( GRID_WIDTH / $grid ) ) * $grid;
		$startY = rand( 0, round( GRID_HEIGHT / $grid ) ) * $grid;
		$endX = $startX + rand( 2, 4 );
		$endY = $startY + rand( 2, 4 );
		for ( $y = $startY; $y <= $endY; $y ++ ) {
			for ( $x = $startX; $x <= $endX; $x ++ ) {
				$world[$y][$x] = TL_PARK;
			}
		}

	}

}




function generate_images() {

	for( $i = 1; $i <= ITEM_COUNT; $i ++ ) {

		$filename = './generated/' . $i . '.png';

		if ( file_exists( $filename ) && KEEP_EXISTING ) {
			continue;
		}

		$img = imagecreatetruecolor( WIDTH, HEIGHT );

		generate_world();
		draw_world( $img );

		imagepng( $img, $filename );
		imagedestroy( $img );

	}

}
function draw_world( $img ) {

	global $world;
	for( $y = 0; $y < GRID_HEIGHT; $y ++ ) {
		for( $x = 0; $x < GRID_WIDTH; $x ++ ) {

			$tl_x_position = $x * TL_SIZE * MULT;
			$tl_y_position = $y * TL_SIZE * MULT;

			draw_tile( $img, $world[$y][$x], $tl_x_position, $tl_y_position );

		}
	}

}

function show_images() {

	$images = glob( './generated/*.png' );

	echo '<div class="images">';

	foreach( $images as $image ) {

		$name = str_replace( array( './generated/', '.png' ), '', $image );
		echo '<div class="image">';
		printf( '<img src="%s" />', $image );
		printf( '<span class="">%s</span>', $name );
		echo '</div>';

	}

	echo '</div>';

}



function draw_tile( $img, $tl_type, $x, $y ) {

	global $tl_image;
	$tl = array( 0, 0 );

	if ( TL_DEFAULT === $tl_type ) {
		if ( rand( 0, 100 ) > 50 ) {
			$tl = array( rand( 0, 1 ), 2 );
		}
	}

	if ( TL_ROAD === $tl_type ) {
		$tl = array( 1, 0 );
	}

	if ( TL_PARK === $tl_type ) {
		$tl = array( rand( 0, 2 ), 1 );
	}

	imagecopyresized(
		$img, $tl_image,						// images.
		$x, $y,									// position on the image we are drawing.
		$tl[0] * TL_SIZE,					// X position of the tile to draw.
		$tl[1] * TL_SIZE,					// Y position of the tile to draw.
		TL_SIZE * MULT, TL_SIZE * MULT,		// Dimensions to copy the tile to.
		TL_SIZE, TL_SIZE					// Size of the tile on the source image.
	);

}

?>
<style>
body { margin: 0; padding: 0; }
img { border: 1em solid white; max-width: 100%; }
.images { show: flex; flex-wrap: wrap; }
.image { flex-grow: 1; position: relative; }
span { position: absolute; top: 1em; left: 1em; padding: 2px 10px; background: white; font-size: 12px; font-weight: bold; }
</style>