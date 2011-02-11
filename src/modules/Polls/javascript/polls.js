// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

/**
 * Submit a poll vote
 *
 *@params none;
 *@return none;
 */
function pollvote()
{
    Element.update('pollvoteinfo', recordingvote);
    
    var pars = Form.serialize('pollvoteform');
    
    new Zikula.Ajax.Request(
        "ajax.php?module=Polls&func=vote", 
        {
            method: 'post', 
            parameters: pars, 
            //authid: 'permissionsauthid',
            onComplete: pollsvote_response
        });
}

/**
 * Ajax response function for the vote: show the result
 *
 *@params none;
 *@return none;
 */
function pollsvote_response(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }
    
    var data = req.getData();
    
    Element.update('pollblockcontent', data.result);
}
