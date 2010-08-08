/**
 * Polls Module for Zikula
 *
 * @copyright (c) 2010, Mark West
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Submit a poll vote
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function pollvote()
{
    Element.update('pollvoteinfo', recordingvote);
    var pars = "module=Polls&func=vote&"
               + Form.serialize('pollvoteform');
    var myAjax = new Ajax.Request(
        document.location.pnbaseURL+'ajax.php', 
        {
            method: 'post', 
            parameters: pars, 
            onComplete: pollsvote_response
        });
}

/**
 * Ajax response function for the vote: show the result
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function pollsvote_response(req)
{
    if(req.status != 200 ) { 
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    Element.update('pollblockcontent', json.result);
}
