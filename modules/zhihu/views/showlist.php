<html>
    <head>
        <link href="<?php echo site_url('asset/css/bootstrap.css');?>" rel="stylesheet">
        <link href="<?php echo  site_url('asset/css/styles.css');?>" rel="stylesheet">
        <link href="<?php echo site_url('asset/css.zhihu.css');?>" rel="stylesheet">
        <!--<link href="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js" rel="stylesheet">-->
        <script src="<?php echo site_url('asset/js/jquery.js');?>" ></script>
        <script src="<?php echo site_url('asset/js/bootstrap.min.js');?>" ></script>
    </head>
    <body>
        <div class="container-fluid">    
            
            <div class="row-fluid">
                <nav class="navbar navbar-default navbar-fixed-top">
		<div class="span12">
			<div class="navbar navbar-inverse">
				<div class="navbar-inner">
					<div class="container-fluid">
						 <a data-target=".navbar-responsive-collapse" data-toggle="collapse" class="btn btn-navbar"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></a> <a href="#" class="brand">LERO_LIN 博客</a>
						<div class="nav-collapse collapse navbar-responsive-collapse">
							<ul class="nav nav-pills">
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
                    <?php foreach ($list as $info) {?>
                    <div class="media well">
                        <a href="<?php echo site_url('zhihu/get_content').'?id='.$info['articleid'];?>" class="pull-left"><img src="<?php echo $info['imageurl'];?>" class="media-object" alt='' /></a>
				<div class="media-body">
					<h4 class="media-heading">
                                              <?php echo $info['title'];?>
					</h4> <?php echo $info['date'];?>
				</div>
			</div>
                    <?php }?>
			<div class="pagination pagination-centered">
                                <?php if($current_page<=1){$up_page = "上一页";}  else {$p=$current_page-1;$url1 =  site_url("zhihu/show_list?page=").$p; $up_page="<a href='{$url}'>上一页</a>";} ?>
                                <?php $s =$total/10;  if($current_page>=$s){$down_page="下一页";}else{$p=$current_page+1;$url2=  site_url("zhihu/show_list?page=").$p; $down_page=$url;}?>
				<ul>
                                    <li>
                                        <a href="<?php echo site_url('zhihu/show_list?page=1');?>">首  页</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo $url1;?>">上一页</a>
                                        
                                    </li>
					
                                    <li>
                                        <a href="<?php echo $url2;?>" >下一页</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo site_url('zhihu/show_list?page=').intval($s +1);?>">尾  页</a>
                                    </li>
				</ul>
			</div>
		</div>
		<div class="span4">
			<blockquote>
				<p>
					THINKING LEARNING
				</p> <small>KEY : <cite>lero_lin</cite></small>
			</blockquote>
                        <blockquote>
                            <p>
                                <a href="<?php echo site_url('zhihu/daily_update');?>" onclick="return confirm('是否确认进行更新')">检查更新</a>
                            </p>
                        </blockquote>
		</div>
	</div>
            
            
            
    </div>
</body>        
</html>      