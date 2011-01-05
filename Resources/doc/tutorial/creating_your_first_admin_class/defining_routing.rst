Defining the routing
====================

Each entity required 6 routes :

- list
- create
- batch
- update
- edit
- delete

For now, there is no implemented way to add admin routes, so we need to define them in a routing files.

As we need to edit a Post, Comment and Tag, the final routing file looks like this :

..

    <?xml version="1.0" encoding="UTF-8" ?>

    <routes xmlns="http://www.symfony-project.org/schema/routing" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.symfony-project.org/schema/routing http://www.symfony-project.org/schema/routing/routing-1.0.xsd">

        <!-- POST CONTROLLER -->
        <route id="news_post_admin_list" pattern="/post">

            <default key="_controller">NewsBundle:PostAdmin:list</default>

        </route>

        <route id="news_post_admin_create" pattern="/post/create">

            <default key="_controller">NewsBundle:PostAdmin:create</default>

        </route>

        <route id="news_post_admin_batch" pattern="/post/batch">

            <default key="_controller">NewsBundle:PostAdmin:batch</default>

        </route>

        <route id="news_post_admin_update" pattern="/post/update">

            <default key="_controller">NewsBundle:PostAdmin:update</default>

        </route>

        <route id="news_post_admin_edit" pattern="/post/:id/edit">

            <default key="_controller">NewsBundle:PostAdmin:edit</default>

        </route>

        <route id="news_post_admin_delete" pattern="/post/:id/delete">

            <default key="_controller">NewsBundle:PostAdmin:delete</default>

        </route>


        <!-- TAG CONTROLLER -->
        <route id="news_tag_admin_list" pattern="/tag">

            <default key="_controller">NewsBundle:TagAdmin:list</default>

        </route>

        <route id="news_tag_admin_create" pattern="/tag/create">

            <default key="_controller">NewsBundle:TagAdmin:create</default>

        </route>

        <route id="news_tag_admin_batch" pattern="/tag/batch">

            <default key="_controller">NewsBundle:TagAdmin:batch</default>

        </route>

        <route id="news_tag_admin_update" pattern="/tag/update">

            <default key="_controller">NewsBundle:TagAdmin:update</default>

        </route>

        <route id="news_tag_admin_edit" pattern="/tag/:id/edit">

            <default key="_controller">NewsBundle:TagAdmin:edit</default>

        </route>

        <route id="news_tag_admin_delete" pattern="/delete/:id/delete">

            <default key="_controller">NewsBundle:TagAdmin:delete</default>

        </route>

        <!-- COMMENT CONTROLLER -->
        <route id="news_comment_admin_list" pattern="/comment">

            <default key="_controller">NewsBundle:CommentAdmin:list</default>

        </route>

        <route id="news_comment_admin_create" pattern="/comment/create">

            <default key="_controller">NewsBundle:CommentAdmin:create</default>

        </route>

        <route id="news_comment_admin_batch" pattern="/comment/batch">

            <default key="_controller">NewsBundle:CommentAdmin:batch</default>

        </route>

        <route id="news_comment_admin_update" pattern="/comment/update">

            <default key="_controller">NewsBundle:CommentAdmin:update</default>

        </route>

        <route id="news_comment_admin_edit" pattern="/comment/:id/edit">

            <default key="_controller">NewsBundle:CommentAdmin:edit</default>

        </route>

        <route id="news_comment_admin_delete" pattern="/comment/:id/delete">

            <default key="_controller">NewsBundle:CommentAdmin:delete</default>

        </route>

    </routes>


As the routing is now defined we can create the CRUD Controller for each Entity