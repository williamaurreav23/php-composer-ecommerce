<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>PHP E-COMMERCE</title>
		<link rel="stylesheet" type="text/css" href="../node_modules/@fortawesome/fontawesome-free/css/all.css">
		<link rel="stylesheet" type="text/css" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="../assets/css/app.css">
	</head>
	<body>
		<nav class="site-header sticky-top py-1">
			<div class="container d-flex flex-column flex-md-row justify-content-between">
				<a class="py-2" href="/">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="d-block mx-auto" role="img" viewBox="0 0 24 24" focusable="false"><title>Product</title><circle cx="12" cy="12" r="10"/><path d="M14.31 8l5.74 9.94M9.69 8h11.48M7.38 12l5.74-9.94M9.69 16L3.95 6.06M14.31 16H2.83m13.79-4l-5.74 9.94"/></svg>
				</a>
				<a class="py-2 d-none d-md-inline-block" href="/">Home</a>
				<a class="py-2 d-none d-md-inline-block" href="/productlist">Products</a>
				<a class="py-2 d-none d-md-inline-block" href="/cart">Cart</a>

				<?php if(isset($_SESSION['login'])): ?>
					<a class="py-2 d-none d-md-inline-block" href="/checkout">Checkout</a>
					<a class="py-2 d-none d-md-inline-block" href="/logout">Logout</a>
				<?php else: ?>
					<a class="py-2 d-none d-md-inline-block" href="/login">Login</a>
					<a class="py-2 d-none d-md-inline-block" href="/register">Register</a>
				<?php endif; ?>
			</div>
		</nav>