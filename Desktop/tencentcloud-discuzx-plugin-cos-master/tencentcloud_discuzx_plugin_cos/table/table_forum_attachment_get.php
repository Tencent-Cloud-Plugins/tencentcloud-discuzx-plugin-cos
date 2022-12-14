<?php

if (! defined('IN_DISCUZ')) {
    exit('Acccess Denied');
}

class table_forum_attachment_get extends discuz_table
{

    public function __construct()
    {
        $this->_table = '';
        $this->_pk = 'aid';
        
        parent::__construct();
    }

    public function table_traversal($tableid, $start = 0, $limit = 0, $sort = '')
    {
        return DB::fetch_all('SELECT aid,attachment,filesize FROM ' . DB::table($this->table_fetch_compare($tableid)) . ' ' . ($sort ? ' ORDER BY ' . DB::order($this->_pk, $sort) : '') . DB::limit($start, $limit), null, $this->_pk ? $this->_pk : '');
    }

    private function table_fetch_compare($tableid)
    {
        if (! is_numeric($tableid)) {
            list ($idtype, $id) = explode(':', $tableid);
            if ($idtype == 'aid') {
                $aid = dintval($id);
                $tableid = DB::result_first("SELECT tableid FROM " . DB::table('forum_attachment') . " WHERE aid='$aid'");
            } elseif ($idtype == 'tid') {
                $tid = (string) $id;
                $tableid = dintval($tid{strlen($tid) - 1});
            } elseif ($idtype == 'pid') {
                $pid = dintval($id);
                $tableid = DB::result_first("SELECT tableid FROM " . DB::table('forum_attachment') . " WHERE pid='$pid' LIMIT 1");
                $tableid = $tableid >= 0 && $tableid < 10 ? intval($tableid) : 127;
            }
        }
        if ($tableid >= 0 && $tableid < 10) {
            return 'forum_attachment_' . intval($tableid);
        } elseif ($tableid == 127) {
            return 'forum_attachment_unused';
        } else {
            throw new DbException('Table forum_attachment_' . $this->_table . ' has not exists');
        }
    }
}
    		  	  		  	  		     	  			     		   		     		       	  		 	    		   		     		       	  	       		   		     		       	 	   	    		   		     		       	  				    		   		     		       	 	   	    		   		     		       	  				    		   		     		       	 	   	    		   		     		       	  			     		 	      	  		  	  		     	
?>