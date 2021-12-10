<?php
#注册插件
RegisterPlugin("mz_w3cHelper", "ActivePlugin_mz_w3cHelper");

function ActivePlugin_mz_w3cHelper()
{
  Add_Filter_Plugin('Filter_Plugin_Zbp_BuildTemplate', 'mz_w3cHelper_GenTpl');
  Add_Filter_Plugin('Filter_Plugin_Cmd_Begin', 'mz_w3cHelper_Cmd_Begin');
}
function mz_w3cHelper_Cmd_Begin()
{
  global $action;
  if (!HasNameInString("PluginEnb|ThemeSet", $action)) {
    return;
  }
  $name = GetVars('name', 'GET', "");
  $theme = GetVars('theme', 'POST', "");
  if ('mz_w3cHelper' === "{$name}{$theme}") {
    Remove_Filter_Plugin('Filter_Plugin_Cmd_Begin', 'AppCentre_Cmd_Begin');
  }
}
function mz_w3cHelper_GenTpl(&$templates)
{
  $tplCont = file_get_contents(mz_w3cHelper_Path("tpl-def"));
  $templates["plugin_mz_w3cHelper_def"] = $tplCont;
  $tplCont = file_get_contents(mz_w3cHelper_Path("tpl-link"));
  $templates["plugin_mz_w3cHelper_link"] = $tplCont;
}
function mz_w3cHelper_Path($file, $t = 'path')
{
  global $zbp;
  $result = $zbp->$t . 'zb_users/plugin/mz_w3cHelper/';
  switch ($file) {
    case 'tpl-def':
      return $result . 'tpl/def.php';
      break;
    case 'tpl-link':
      return $result . 'tpl/link.php';
      break;
    case 'main':
      return $result . 'main.php';
      break;
    default:
      return $result . $file;
  }
}
function InstallPlugin_mz_w3cHelper()
{
  global $zbp;
  $zbp->BuildTemplate();
}
function UninstallPlugin_mz_w3cHelper()
{
}

// fuctions
function mz_w3cHelper_InitArrMap()
{
  global $zbp;
  return array(
    "index" => $zbp->host,
    "category" => "",
    "tag" => "",
    "date" => "",
    "article" => "",
    "page" => "",
  );
}

function mz_w3cHelper_a($href, $title, $text = "", $w3c = false)
{
  if (empty($text)) {
    $text = $href;
  }
  if ($w3c) {
    $href = "https://validator.w3.org/nu/?showsource=yes&doc=" . urlencode($href);
  }
  return "<a target=\"_blank\" href=\"{$href}\" title=\"{$title}\">$text</a>";
}

function mz_w3cHelper_Main()
{
  global $zbp;
  $rltMaps = array();
  $articles = mz_w3cHelper_GetPostList();
  $articles = array_merge($articles, mz_w3cHelper_GetPostList(1, 1));

  foreach ($articles as $article) {
    $curArrMap = mz_w3cHelper_InitArrMap();
    mz_w3cHelper_ArticleInfo($article, $curArrMap);
    $rltMaps[] = $curArrMap;
  }

  $rltMaps = mz_w3cHelper_MergeRltMaps($rltMaps);

  $zbp->template->SetTags('rltMaps', $rltMaps);
  $rltHtml = $zbp->template->Output("plugin_mz_w3cHelper_def");

  return $rltHtml;
}

// 合并结果
function mz_w3cHelper_MergeRltMaps($rltMaps)
{
  $relRltMaps = array();
  foreach ($rltMaps as $arrMap) {
    foreach ($arrMap as $key => $value) {
      if (empty($value)) {
        continue;
      }
      $value = urldecode($value);
      if (!isset($relRltMaps[$key])) {
        $relRltMaps[$key] = array($value);
        continue;
      }
      if (in_array($value, $relRltMaps[$key])) {
        continue;
      } else {
        $relRltMaps[$key][] = $value;
      }
    }
  }
  return $relRltMaps;
}

/**
 * 获取文章的分类、tag，或者日期链接
 *
 * @param object $article
 * @param array $arrMap
 * @return void
 */
function mz_w3cHelper_ArticleInfo($article, &$arrMap)
{
  global $zbp;
  if ($article->Type == 0) {
    $arrMap["article"] = $article->Url;
    $category = $article->Category;
    $arrMap["category"] = $category->Url;
    if (!empty($article->Tag)) {
      $FirstTag = $article->FirstTag;
      $arrMap["tag"] = $FirstTag->Url;
    }
  } else {
    $arrMap["page"] = $article->Url;
  }
  $urlForDate = new UrlRule($zbp->option['ZC_DATE_REGEX']);
  $urlForDate->Rules['{%date%}'] = $article->Time('Y-m-d');
  $arrMap["date"] = $urlForDate->Make();
}

/**
 * 获取文章或页面
 *
 * @param integer $nums
 * @param integer $type
 * @return array
 */
function mz_w3cHelper_GetPostList($nums = 3, $type = 0)
{
  global $zbp;
  $w = array();
  $w[] = array('=', 'log_Type', $type);

  $order = array('log_ViewNums' => 'DESC');
  $limit = $nums;

  $postList = $zbp->GetPostList(
    array('*'),
    $w,
    $order,
    $limit,
    null
  );
  return $postList;
}
