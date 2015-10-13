<html>
    <head>
        <link href="<?php echo site_url('asset/css/bootstrap.css');?>" rel="stylesheet">
        <link href="<?php echo  site_url('asset/css/styles.css');?>" rel="stylesheet">
        <link href="<?php echo site_url('asset/css.zhihu.css');?>" rel="stylesheet">
        <!--<link href="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js" rel="stylesheet">-->
        <script href="<?php echo site_url('asset/js/jquery.js');?>" rel="stylesheet"></script>
        <script href="<?php echo site_url('asset/js/bootstrap2.min.js');?>" rel="stylesheet"></script>
    </head>
    <body>
<div class="container-fluid">
    
        <div class="row-fluid ">
            <nav class="navbar navbar-default navbar-fixed-top">
		<div class="span12">
			<div class="navbar navbar-inverse">
				<div class="navbar-inner">
					<div class="container-fluid">
						 <a data-target=".navbar-responsive-collapse" data-toggle="collapse" class="btn btn-navbar"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></a> <a href="#" class="brand">LERO_LIN 博客</a>
						<div class="nav-collapse collapse navbar-responsive-collapse">
							<ul class="nav">
								<li >
									<a href="#">主页</a>
								</li>
								<li class="active">
                                                                    <a href="<?php echo site_url('zhihu/show_list').'?limit=10'?>" target="_parent">知乎</a>
								</li>
								<li class="dropdown" >
									 <a data-toggle="dropdown" class="dropdown-toggle" href="#">下拉菜单<strong class="caret"></strong></a>
									<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
										<li>
											<a href="#">下拉导航1</a>
										</li>
										<li>
											<a href="#">下拉导航2</a>
										</li>
										<li>
											<a href="#">其他</a>
										</li>
										<li class="divider">
										</li>
										<li class="nav-header">
											标签
										</li>
										<li>
											<a href="#">链接1</a>
										</li>
										<li>
											<a href="#">链接2</a>
										</li>
									</ul>
								</li>
							</ul>
							<ul class="nav pull-right">
								<li>
                                                                    <a href="<?php echo site_url('admin/logout');?>">退出</a>
								</li>
								<li class="divider-vertical">
								</li>
								<li class="dropdown">
									 <a data-toggle="dropdown" class="dropdown-toggle" href="#">下拉菜单<strong class="caret"></strong></a>
									<ul class="dropdown-menu">
										<li>
											<a href="#">下拉导航1</a>
										</li>
										<li>
											<a href="#">下拉导航2</a>
										</li>
										<li>
											<a href="#">其他</a>
										</li>
										<li class="divider">
										</li>
										<li>
											<a href="#">链接3</a>
										</li>
									</ul>
								</li>
							</ul>
						</div>
						
					</div>
				</div>
				
			</div>
		</div>
            </nav>
	</div>
        <div class="row-fluid">
		<div class="span2">
		</div>
		<div class="span6">
		</div>
		<div class="span4">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span2">
		</div>
		<div class="span6">
		</div>
		<div class="span4">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span2">
		</div>
		<div class="span6">
			<h2>
			</h2>
			<p>
                            <?php echo $zhihu['body'];?>
                                <img src="<?php echo 'http://www.beihaiw.com/pic.php?url='.$zhihu['imageurl'];?>" alt="zhihu">
                                
                        </p>
			<p>
				<a class="btn" href="#">返回顶部</a>
			</p>
			
		</div>
                
		<div class="span4">
			<blockquote>
				<p>
					THINKING LEARNING
				</p> <small>KEY : <cite>lero_lin</cite></small>
			</blockquote>
		</div>
	</div>
</div>
</body>        
</html>      