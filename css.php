
<!DOCTYPE html>
<html lang="en">
	<head>
<style>
*, *:after, *:before { -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; }

body {
	background: #fbc73b;
	font-family: 'Lato', Arial, sans-serif;
	color: #000;
}

.stage {
	list-style: none;
	padding: 0;
}

/*************************************
Build the scene and rotate on hover
**************************************/

.scene {
	width: 250px;
	height: 250px;
	margin: 100px;
	float: left;
	-webkit-perspective: 1500px;
	-moz-perspective: 1500px;
	perspective: 1500px;
}

.cube {
	width: 250px;
	height: 250px;
	-webkit-transform-style: preserve-3d;
	-moz-transform-style: preserve-3d;
	transform-style: preserve-3d;
	-webkit-transform: translateZ(-130px);
	-moz-transform: translateZ(-130px);
	transform: translateZ(-130px);
	-webkit-transition: -webkit-transform 350ms;
	-moz-transition: -moz-transform 350ms;
	transition: transform 350ms;
}

.cube:hover {
	-webkit-transform: rotateY(-90deg) translateZ(20px);
	-moz-transform: rotateY(-90deg) translateZ(20px);
	transform: rotateY(-90deg) translateZ(20px);
}

/*************************************
Transform and style the two planes
**************************************/

.cube .poster, 
.cube .info {
	position: absolute;
	width: 250px;
	height: 250px;
	background-color: #fff;
	-webkit-backface-visibility: hidden;
	-moz-backface-visibility: hidden;
	backface-visibility: hidden;
}

.cube .poster  {
	-webkit-transform: translateZ(130px);
	-moz-transform: translateZ(130px);
	transform: translateZ(130px);
	background-size: cover;
	background-repeat: no-repeat;
}

.cube .info {
	-webkit-transform: rotateY(90deg) translateZ(130px);
	-moz-transform: rotateY(90deg) translateZ(130px);
	transform: rotateY(90deg) translateZ(130px);
	border: 1px solid #B8B5B5;
	font-size: 0.75em;
}

/*************************************
Shadow beneath the 3D object
**************************************/

.cube::after {
	content: '';
	width: 260px;
	height: 260px;
	position: absolute;
	bottom: 0;
	box-shadow: 0 30px 50px rgba(0,0,0,0.3);
	-webkit-transform-origin: 100% 100%;
	-moz-transform-origin: 100% 100%;
	transform-origin: 100% 100%;
	-webkit-transform: rotateX(90deg) translateY(130px);
	-moz-transform: rotateX(90deg) translateY(130px);
	transform: rotateX(90deg) translateY(130px);
	-webkit-transition: box-shadow 350ms;
	-moz-transition: box-shadow 350ms;
	transition: box-shadow 350ms;
}

.cube:hover::after {
	box-shadow: 20px -5px 50px rgba(0,0,0,0.3);
}

/*************************************
Movie information
**************************************/

.info header {
	color: #FFF;
	padding: 7px 10px;
	font-weight: bold;
	height: 195px;
	background-size: contain;
	background-repeat: no-repeat;
	text-shadow: 0px 1px 1px rgba(0,0,0,1);
}

.info header h1 {
	margin: 0 0 2px;
	font-size: 1.4em;
}

.info header .rating {
	border: 1px solid #FFF;
	padding: 0px 3px;
}

.info p {
	padding: 1.2em 1.4em;
	margin: 2px 0 0;
	font-weight: 700;
	color: #666;
	line-height: 1.4em;
	border-top: 10px solid #555;
}

/*************************************
Generate "lighting" using box shadows
**************************************/

.cube .poster,
.cube .info,
.cube .info header {
	-webkit-transition: box-shadow 350ms;
	-moz-transition: box-shadow 350ms;
	transition: box-shadow 350ms;
}

.cube .poster {
	box-shadow: inset 0px 0px 40px rgba(255,255,255,0);
}

.cube:hover .poster {
	box-shadow: inset 300px 0px 40px rgba(255,255,255,0.8);
}

.cube .info, 
.cube .info header {
	box-shadow: inset -300px 0px 40px rgba(0,0,0,0.5);
}

.cube:hover .info, 
.cube:hover .info header {
	box-shadow: inset 0px 0px 40px rgba(0,0,0,0);
}



/*************************************
Media Queries
**************************************/
@media screen and (max-width: 60.75em){
	.scene {
		float: none;
		margin: 30px auto 60px;
	}
}
</style>
</head>
<body>


	<ul class="stage clearfix">

		<li class="scene">
			<div class="cube">
				<div class="side1">
					Side A
				</div>
				<div class="side2">
					Side B
				</div>
			</div>
		</li>

	</ul>



</body>
</html>