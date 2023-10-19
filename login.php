<!DOCTYPE html>
<html lang="pt-br">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<title>Intranet - Login</title>
		<meta name="generator" content="" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta http-equiv="X-Frame-Options" content="deny">
		
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<link rel="stylesheet" href="<?php echo $config['raizS3'];?>css/adminlte.min.css">
	</head>
<body class="hold-transition login-page" style="background: url('<?php echo $config['raizS3'];?>imagens/trilhas.jpeg'); background-size: cover;">
<div class="login-box">
  <div class="login-logo">
    <span style="color: #CCCCCC; font-weight: bold; font-size: x-large;">intranet Tomaz</span>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Fa&ccedil;a login para iniciar sua sess&atilde;o</p>

      <form action="index.php" method="post">
      	<input type="hidden" name="login" value="twslogin">
        <div class="input-group mb-3">
          <input type="email" class="form-control" placeholder="Email" name="usuario">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Senha" name="senha">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <p class="mb-1">
        <a href="forgot-password.html">Esqueci minha senha</a>
      </p>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="<?php echo $config['raizS3'];?>plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?php echo $config['raizS3'];?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo $config['raizS3'];?>js/adminlte.min.js"></script>
</body>
</html>