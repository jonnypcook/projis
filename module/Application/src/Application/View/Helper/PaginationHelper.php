<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class PaginationHelper extends AbstractHelper
{
    private $resultsPerPage;
    private $totalResults;
    private $results;
    private $baseUrl;
    private $paging;
    private $page;

    public function __invoke($totalRows, $page, $baseUrl, $resultsPerPage = 10)
    {
        $this->resultsPerPage = $resultsPerPage;
        $this->totalResults = $totalRows;
        $this->baseUrl = $baseUrl;
        $this->page = $page;

        return $this->generatePaging($page);
    }

    /**
     * Generate paging html
     */
    private function generatePaging($page)
    {
        # Get total page count
        $pages = ceil($this->totalResults / $this->resultsPerPage);

        # Don't show pagination if there's only one page
        if ($pages == 1) {
            return;
        }

        # Show back to first page if not first page
        if ($this->page != 1) {
            $this->paging = '<a href="' . $this->baseUrl . 'page-1"><<</a>';
        }

        # Create a link for each page

        $pageCount = $page > 2 ? $page - 2 : 1;
        $displayPaging = 0;
        while ($pageCount <= $pages) {
            if ($displayPaging >= 5) {
                break;
            }
            if ($page == $pageCount) {
                $this->paging .= '<span>' . $pageCount . '</span>';
            }
            else {
                $this->paging .= '<a href="' . $this->baseUrl . 'page-' . $pageCount . '">' . $pageCount . '</a>';
            }
            $pageCount++;
            $displayPaging++;
        }

        # Show go to last page option if not the last page
        if ($this->page != $pages) {
            $this->paging .= '<a href="' . $this->baseUrl . 'page-' . $pages . '">>></a>';
        }

        return $this->paging;
    }
}
