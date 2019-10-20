<h1>Article</h1>

<p>
	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent est dui, mattis nec ornare dapibus, volutpat vitae felis. Ut a congue velit. Nullam ut lorem in metus efficitur tempor. Donec eu lectus quis ligula dapibus hendrerit quis at massa. Curabitur hendrerit velit quis convallis pretium. Etiam a odio imperdiet libero lobortis condimentum. Integer nisi dui, maximus a urna vel, pulvinar laoreet eros. Donec mollis pharetra diam, non ultrices sapien pretium nec. Aliquam erat volutpat. Vestibulum tincidunt risus ut convallis posuere.
</p>

<form action="<?php echo $this->base_url ?>/comment/create" method="POST">
	<fieldset style="width: 300px">
		<legend>Add a Comment:</legend>
		<input name="email" type="email" value="osh@gmail.com" placeholder="Email here" style="width: 100%" />
		<textarea name="body" val="test1" placeholder="Your comment here" style="width: 100%; height: 100px"></textarea>
		<button type="submit">Post</button>
	</fieldset>
</form>

<h2>Comments:</h2>
<ul>
<?php foreach ($view->comments() as $comment): ?>
	<li>
		<strong><?php echo $comment['email'] ?>: </strong>
		<p><?php echo $comment['body'] ?><p>
	</li>
<?php endforeach ?>
</ul>
