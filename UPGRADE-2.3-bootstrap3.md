UPGRADE FROM 2.2 to 2.3 (Bootstrap3 theming)
============================================

### Dependencies

You'll need to follow the dependencies upgrade instructions.

### Templates

 - ``standard_layout.html.twig`` has been updated (refactored layout accordingly to the theme). Some blocks have been moved, and the sonata_side_nav twig block became the main menu.
 - Admin's sidemenus are now tab-menus; former tab-menus are now collapsible items.

### Admin classes

 - ``configureSideMenu`` and ``buildSideMenu`` methods of the ``Admin`` class have been deprecated; they are replaced by ``configureTabMenu`` and ``buildTabMenu``.

