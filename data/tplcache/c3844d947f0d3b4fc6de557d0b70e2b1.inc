
   {dede:match}<h1>[内容]</h1>{/dede:match}
   {dede:trim replace=""}正文{/dede:trim}
{dede:trim replace="第"}第(.*)卷(.{0,2})第{/dede:trim}
{dede:trim replace="第"}第(.*)部(.{0,2})第{/dede:trim}
   {dede:function}{/dede:function}
