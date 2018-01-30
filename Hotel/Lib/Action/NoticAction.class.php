<?php
	class NoticAction extends BaseAction{
		public function index(){
			$this->info=M('Notic')->where('id='.$this->_get('id'))->find();
			$this->display();
		}
		public function lists(){
			$com=M('Notic');
			import('ORG.Util.Page');
			$count=$com->where($map)->count();
			$this->total=$count;
			$page=new Page($count,8);
			$show=$page->show();
			$content=$com->where($map)
			->limit($page->firstRow.','.$page->listRows)
			->order('id desc')
			->select();
			$this->assign('list',$content);
			$this->assign('page',$show);
			
			$this->display();			
		}
	}