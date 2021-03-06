jQuery(document).ready(function ($) {
    $('.ct-demo-import').click(function () {
        var $a = $(this);
        $('.importDescription').fadeOut('fast', function () {
            $('.importLoader').fadeIn('fast');

            $.post(ajaxurl, {'action': $a.attr('data-action'), 'dir': $a.attr('data-dir')}, function (html) {
                $('.importLoader').fadeOut('fast', function () {
                    if ($(location).attr('href').indexOf('ct_dev') < 0) {
                        import_demo_images($a.attr('data-dir'), $('.importDescription').html(html));
                    } else {
                        $('.importDescription').html(html).show();
                    }
                });
            });
        });
        return false;
    });
});

/**
 * Imports images via AJAX
 * @param importDir
 */

function import_demo_images(importDir, onceDone) {

    var xml = '';
    jQuery.post(
        ajaxurl,
        {action: 'ct-demo-images-xml', dir: importDir, _ajax_nonce: ctSecurity.nonce,},
        function (data) {
            xml = data;
        }).done(function () {
            var progressBar = jQuery("#ct-demo-importer-progressbar"),
                progressLabel = jQuery("#ct-demo-importer-progresslabel"),
                divOutput = jQuery("#ct-demo-importer-output");

            jQuery(function () {
                progressBar.progressbar({
                    value: false
                });
                progressLabel.text('Parsing ...');
            });
            var url = [],
                title = [],
                link = [],
                pubDate = [],
                creator = [],
                guid = [],
                postID = [],
                postDate = [],
                postDateGMT = [],
                commentStatus = [],
                pingStatus = [],
                postName = [],
                status = [],
                postParent = [],
                menuOrder = [],
                postType = [],
                postPassword = [],
                isSticky = [];

            jQuery(xml).find('item').each(function () {

                var xml_post_type = jQuery(this).find('wp\\:post_type, post_type').text();

                if (xml_post_type == 'attachment') { // We're only looking for image attachments.
                    url.push(jQuery(this).find('wp\\:attachment_url, attachment_url').text());
                    title.push(jQuery(this).find('title').text());
                    link.push(jQuery(this).find('link').text());
                    pubDate.push(jQuery(this).find('pubDate').text());
                    creator.push(jQuery(this).find('dc\\:creator, creator').text());
                    guid.push(jQuery(this).find('guid').text());
                    postID.push(jQuery(this).find('wp\\:post_id, post_id').text());
                    postDate.push(jQuery(this).find('wp\\:post_date, post_date').text());
                    postDateGMT.push(jQuery(this).find('wp\\:post_date_gmt, post_date_gmt').text());
                    commentStatus.push(jQuery(this).find('wp\\:comment_status, comment_status').text());
                    pingStatus.push(jQuery(this).find('wp\\:ping_status, ping_status').text());
                    postName.push(jQuery(this).find('wp\\:post_name, post_name').text());
                    status.push(jQuery(this).find('wp\\:status, status').text());
                    postParent.push(jQuery(this).find('wp\\:post_parent, post_parent').text());
                    menuOrder.push(jQuery(this).find('wp\\:menu_order, menu_order').text());
                    postType.push(xml_post_type);
                    postPassword.push(jQuery(this).find('wp\\:post_password, post_password').text());
                    isSticky.push(jQuery(this).find('wp\\:is_sticky, is_sticky').text());
                }
            });

            var pbMax = postType.length;

            jQuery(function () {
                progressBar.progressbar({
                    value: 0,
                    max: postType.length,
                    complete: function () {
                        jQuery.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'ct-demo-images-upload-completed',
                                _ajax_nonce: ctSecurity.nonce,
                            }
                        }).done(function (data, status, xhr) {
                            progressLabel.text(ctL10n.done);
                        })
                    }
                });
            });

            // Define counter variable outside the import attachments function
            // to keep track of the failed attachments to re-import them.
            var failedAttachments = 0;

            function import_attachments(i) {
                progressLabel.text(ctL10n.importing + '"' + title[i] + '". ' + ctL10n.progress + progressBar.progressbar("value") + "/" + pbMax);
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ct-demo-images-upload',
                        _ajax_nonce: ctSecurity.nonce,
                        //author1: author1,
                        //author2: author2,
                        url: url[i],
                        title: title[i],
                        link: link[i],
                        pubDate: pubDate[i],
                        creator: creator[i],
                        guid: guid[i],
                        post_id: postID[i],
                        post_date: postDate[i],
                        post_date_gmt: postDateGMT[i],
                        comment_status: commentStatus[i],
                        ping_status: pingStatus[i],
                        post_name: postName[i],
                        status: status[i],
                        post_parent: postParent[i],
                        menu_order: menuOrder[i],
                        post_type: postType[i],
                        post_password: postPassword[i],
                        is_sticky: isSticky[i]
                    }
                })
                    .done(function (data, status, xhr) {
                        // Parse the response.
                        var obj = jQuery.parseJSON(data);

                        // If error shows the server did not respond,
                        // try the upload again, to a max of 3 tries.
                        if (obj.message == "Remote server did not respond" && failedAttachments < 25) {
                            failedAttachments++;
                            progressLabel.text(ctL10n.retrying + '"' + title[i] + '". ' + ctL10n.progress + progressBar.progressbar("value") + "/" + pbMax);
                            setTimeout(function () {
                                import_attachments(i);
                            }, 5000);
                        }

                        // If a non-fatal error occurs, note it and move on.
                        else if (obj.type == "error" && !obj.fatal) {
                            divOutput.html(jQuery('<p>' + obj.text + '</p>'));
                            next_image(i);
                        }

                        // If a fatal error occurs, stop the program and print the error to the browser.
                        else if (obj.fatal) {
                            progressBar.progressbar("value", pbMax);
                            progressLabel.text(ctL10n.fatalUpload);
                            divOutput.html(jQuery('<div class="' + obj.type + '">' + obj.text + '</div>'));
                            onceDone.show();
                            return false;
                        }

                        else { // Moving on.
                            next_image(i);
                        }
                    })
                    .fail(function (xhr, status, error) {
                        failedAttachments++;
                        progressLabel.text(ctL10n.retrying + '"' + title[i] + '". ' + ctL10n.progress + progressBar.progressbar("value") + "/" + pbMax);
                        setTimeout(function () {
                            import_attachments(i);
                        }, 5000);
                    });
            }

            function next_image(i) {
                // Increment the internal counter and progress bar.
                i++;
                progressBar.progressbar("value", progressBar.progressbar("value") + 1);
                failedAttachments = 0;

                // If every thing is normal, but we still have posts to process,
                // then continue with the program.
                if (postType[i]) {
                    setTimeout(function () {
                        import_attachments(i)
                    }, 0);
                }

                // Getting this far means there are no more attachments, so stop the program.
                else {
                    onceDone.show();
                    return false;
                }
            }

            if (postType[0]) {
                import_attachments(0);
            } else {
                progressBar.progressbar("value", pbMax);
                progressLabel.text(ctL10n.pbAjaxFail);
                jQuery('<div class="error">' + ctL10n.noAttachments + '</div>').appendTo(divOutput);
            }
        });
}