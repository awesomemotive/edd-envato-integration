<?PHP

// Include Our Custom WP_List_Table Class
require_once('table_class.php');

/*-----------------------------------------------------------------------------------*/
/*	New framework Management Page
/*-----------------------------------------------------------------------------------*/

function eddenvato_manage_page() { ?>

    <div class="wrap">
            
        <div id="icon-edit-pages" class="icon32"><br/></div>
        <h2>Envato Purchase Codes<?PHP if(isset($_GET['uid']) && !isset($_POST['s'])){ ?> ( User ID: <?PHP echo $_GET['uid']; ?> ) <?PHP } ?></h2>
    
        <?PHP
            //Create an instance of our package class...
            $envatoKeyTable = new eddenvato_Table_List();
            //Fetch, prepare, sort, and filter our data...
			if( isset($_POST['s']) ){
				$envatoKeyTable->prepare_items($_POST['s']);
			} else if( isset($_GET['uid']) ) {
				$envatoKeyTable->prepare_items(NULL, stripslashes($_GET['uid']));
			} else {
				$envatoKeyTable->prepare_items();
			}
        ?>
                                
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form method="post">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
          <?php $envatoKeyTable->search_box('Search Codes', 'tc-envato-search'); ?>
        </form>

        <form id="tc-envato-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $envatoKeyTable->display() ?>
        </form>
                        
    </div>

<?php } ?>