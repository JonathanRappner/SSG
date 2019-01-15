<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hjälper till att generera HTML-kod.
 */
class Doodads
{
	protected $CI;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	/**
	 * SKriver ut pagination-kontroller (Föregående, 1, 2, 3, Nästa)
	 *
	 * @param int $page Nuvarande sida
	 * @param int $total_pages Totala antal sidor.
	 * @param string $link_prefix Länk-sträng som kommer före sidnummer.
	 * @param int $max_pages Max antal sidor som pagination listar.
	 * @return void
	 */
	public function pagination($page, $total_pages, $link_prefix, $max_pages = 12)
	{
		$output = '<nav><ul class="pagination">';
		
		//föregående
		$output .= $page > 0
			?
				'<li class="page-item"><a class="page-link" href="'. $link_prefix . 0 .'"><i class="fas fa-step-backward"></i></a></li>
				<li class="page-item"><a class="page-link" href="'. $link_prefix . ($page-1) .'"><i class="fas fa-chevron-left"></i></a></li>'
			:
				'<li class="page-item disabled"><span class="page-link"><i class="fas fa-step-backward"></i></span></li>
				<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>';
		
		//sidlänkar (1, 2, 3, osv.)
		if($total_pages <= $max_pages) //lista alla sidor
		{
			for($i = 0; $i < $total_pages; $i++)
				$output .= 
					'<li class="page-item'. ($page == $i ? ' active' : null) .'">
						<a class="page-link" href="'. $link_prefix . $i .'">'. ($i+1) .'</a>
					</li>';
		}
		else //visa sidor runt on current page istället (max antal: $max_pages)
		{
			$start = max($page - floor($max_pages / 2), 0);
			$end = min($start + $max_pages, $total_pages);
			$start = min($start, $end-$max_pages); //om mindre än hälften av sidorna ligger efter current
			
			for($i=$start; $i<$end; $i++)
				$output .= 
					'<li class="page-item'. ($page == $i ? ' active' : null) .'">
						<a class="page-link" href="'. $link_prefix . $i .'">'. ($i+1) .'</a>
					</li>';
		}
		
		//nästa
		$output .= $page < ($total_pages-1)
			?
				'<li class="page-item"><a class="page-link" href="'. $link_prefix . ($page+1) .'"><i class="fas fa-chevron-right"></i></a></li>
				<li class="page-item"><a class="page-link" href="'. $link_prefix . ($total_pages-1) .'"><i class="fas fa-step-forward"></i></a></i>'
			:
				'<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>
				<li class="page-item disabled"><span class="page-link"><i class="fas fa-step-forward"></i></span></li>';

		$output .= '</ul></nav>';

		return $output;
	}

	/**
	 * Skriver ut grupp-ikon.
	 * Använder en 16px-ikon för desktop och 32px-ikon för mobil.
	 *
	 * @param string $group_code Grupp-kod (ex. 'fa', 'vl')
	 * @return void
	 */
	public function group_icon($group_code)
	{
		//variabler
		$no_icon = array('ja', 'ka', 'gb', 'ib'); //grupper som inte har ikoner
		$icon_string_start = base_url("images/group_icons/$group_code");

		if($group_code != null && !in_array($group_code, $no_icon))
		{
			return
				'<img class="group_icon_16 d-none d-md-inline" src="'. $icon_string_start .'_16.png" />
				<img class="group_icon_32 d-inline d-md-none" src="'. $icon_string_start .'_32.png" />';
		}
		else
			return '<i class="fas fa-question-circle"></i>';
	}
}
?>