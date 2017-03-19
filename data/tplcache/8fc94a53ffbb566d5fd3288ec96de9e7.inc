
   {dede:match}<dd id="contents">[内容]</dd>{/dede:match}
   {dede:trim replace=""}www(.*)com{/dede:trim}
{dede:trim replace=""}<script([^>]*)>(.*)</script>{/dede:trim}
{dede:trim replace=""}<!--(.*)-->{/dede:trim}
{dede:trim replace=""}顶(.{0,2})点(.{0,2})小(.{0,2})说{/dede:trim}
   {dede:function}{/dede:function}
