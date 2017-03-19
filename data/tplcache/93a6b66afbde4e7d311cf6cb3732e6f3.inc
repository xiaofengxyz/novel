
   {dede:match}<div id="title">[内容]</div>{/dede:match}
   {dede:trim replace=""}正文{/dede:trim}
{dede:trim replace="第"}第(.*)卷(.{0,2})第{/dede:trim}
{dede:trim replace="第"}第(.*)部(.{0,2})第{/dede:trim}
   {dede:function}{/dede:function}
