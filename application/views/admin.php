<!DOCTYPE html>
<?php echo $view_file; ?><html>
<head>
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.1.min.js"></script>
	<script type="text/javascript">
	$(document).ready(function() {
//		$_POST = array(
//			array(
//				'skill' => array(
//					'attribute'  => 1,
//					'name'       => 'Test Skill',
//					'unskilled'  => 1,
//					'penalty'    => 1,
//					'rated_only' => 0,
//					'active'     => 1,
//				),
//				'axioms' => array(
//					1 => 0,
//					2 => 0,
//					3 => 0,
//					4 => 0,
//				)
//			)
//		);

		$('input[type=button]').on('click', function() {
			$.ajax({
				url: '/admin/add_skill',
				type: 'post',
				data: {
					skills: [
						{
							skill : {
								attribute: $('[name=attribute]').val(),
								name     : $('[name=name]').val(),
								unskilled: $('[name=unskilled]').val(),
								penalty  : $('[name=penalty]').val(),
								rated_only: $('[name=rated_only]').val(),
								active   : $('[name=active]').val()
							},
							axioms: {
								1 : $('[name=1]').val(),
								2 : $('[name=2]').val(),
								3 : $('[name=3]').val(),
								4 : $('[name=4]').val()
							}
						}
					]
				}
			});
		});
	});
	</script>
</head>
<body>
	<form>
		<select name="attribute">
			<option value="1">Dexterity</option>
			<option value="2">Strength</option>
			<option value="3">Toughness</option>
			<option value="4">Perception</option>
			<option value="5">Mind</option>
			<option value="6">Charisma</option>
			<option value="7">Spirit</option>
			<option value="8">Run</option>
			<option value="9">Swim</option>
			<option value="10">Jump</option>
			<option value="11">Climb</option>
			<option value="12">Lift</option>
			<option value="13">Hold Breath</option>
			<option value="14">Flight</option>
		</select>
		<label>Name:</label><input type="text" name="name" />
		<label>Unskilled:</label><input type="checkbox" name="unskilled" checked />
		<label>Penalty:</label><input type="checkbox" name="penalty" checked />
		<label>Rated Only:</label><input type="checkbox" name="rated_only" />
		<label>Active:</label><input type="checkbox" name="active" checked />


		<br /><label>Magical</label><input type="number" name="1" value="0" />
		<br /><label>Spiritual</label><input type="number" name="2" value="0" />
		<br /><label>Social</label><input type="number" name="3" value="0" />
		<br /><label>Technological</label><input type="number" name="4" value="0" />
	</form>
	<input type="button" value="Submit" />
</body>
</html>