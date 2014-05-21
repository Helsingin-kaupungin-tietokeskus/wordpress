<?php

/**
 *	HRI CKAN search query class
 */

class hri_ckan_query {

	private $calc_rows;
	private $field;
	private $groupby;
	private $leftjoin;
	private $limit;
	private $meta;
	private $order;
	private $prefix;
	private $source;
	private $taxonomies;
	private $query;
	private $query_words;
	private $where;
	private $where_raw;
	private $resultCount = false;
	public $resultsPerPage = 10;
	public $pager = "";
	
	/**
	 * @param string $prefix WP's table prefix
	 * @param boolean $calc_found_rows Add SQL_CALC_FOUND_ROWS to query?
	 * @param string $field
	 * @param boolean|int $source Add source number for UNION sub queries
	 */
	public function __construct( $prefix = 'wp_', $calc_found_rows = true, $field = 'p.ID', $source = false ) {
		$this->prefix = $prefix;
		$this->calc_rows = (boolean) $calc_found_rows;
		$this->field = $field;
		$this->source = ( $source ) ? (int) $source : false;
	}

	/**
	 * @param int $start MySQL LIMIT first value
	 * @param int $end MySQL LIMIT second value
	 * @return void
	 */
	public function set_limit( $start, $end) {
		$start = (int) $start;
		$end = (int) $end;
		$this->limit = "\nLIMIT $start,$end";
	}

	/**
	 * @return void
	 */
	public function no_limit() {
		$this->limit = null;
	}

	/**
	 * @param array $order
	 * @return void
	 */
	public function orderby( $order ) {
		$this->order[] = $order;
	}

	/**
	 * @param array $sort_array
	 * @return void
	 *
	 * sorts:
	 * 1: post_date
	 * 2: post_title
	 * 3: latest comment
	 * 4: rating
	 * 5: comment count
	 * 6: discussion count
	 * 7: latest app
	 *
	 */
	public function set_sorts( $sort_array ) {
		if ( $sort_array[0] == 1 ) {$this->orderby( array( 'p.post_date', 'ASC' ) ); $this->orderby( array( 'p.post_title', 'ASC' ) );}
		if ( $sort_array[0] == -1 ) {$this->orderby( array( 'p.post_date', 'DESC' ) ); $this->orderby( array( 'p.post_title', 'ASC' ) );}

		if ( $sort_array[0] == 2 ) $this->orderby( array( 'p.post_title', 'ASC' ) );
		if ( $sort_array[0] == -2 ) $this->orderby( array( 'p.post_title', 'DESC' ) );

		if ( $sort_array[0] == 3 || $sort_array[0] == -3 ) {

			$this->left_join( "(\n\tSELECT comment_post_ID, MAX( comment_date ) AS comment_date FROM {$this->prefix}comments WHERE comment_approved = 1 GROUP BY comment_post_ID\n) AS c", array( 'c.comment_post_ID = p.ID' ) );

			if($sort_array[0] == 3) $this->orderby( array( 'c.comment_date', 'DESC' ) );
			if($sort_array[0] == 3) $this->orderby( array( 'p.post_title', 'ASC' ) );
			if($sort_array[0] == -3) $this->orderby( array( 'c.comment_date', 'ASC' ) );
			if($sort_array[0] == -3) $this->orderby( array( 'p.post_title', 'ASC' ) );

		}

		if ( $sort_array[0] == 4 || $sort_array[0] == -4 ) {
			$this->left_join( $this->prefix . 'comments c_sort', array( 'c_sort.comment_post_ID = p.ID', 'c_sort.comment_approved = 1' ) );
			$this->left_join( $this->prefix . 'commentmeta m_sort', array( 'c_sort.comment_ID = m_sort.comment_id', 'm_sort.meta_key LIKE \'\_hri\_rating_\'' ) );
			$this->groupby( 'p.ID' );

			if ($sort_array[0] == 4) $this->orderby( array( 'AVG(m_sort.meta_value)', 'ASC' ) );
			if ($sort_array[0] == 4) $this->orderby( array( 'p.post_title', 'ASC' ) );

			if ($sort_array[0] == -4) $this->orderby( array( 'AVG(m_sort.meta_value)', 'DESC' ) );
			if ($sort_array[0] == -4) $this->orderby( array( 'p.post_title', 'ASC' ) );
		}

		if ( $sort_array[0] == 5 || $sort_array[0] == -5 ) {
			if ($sort_array[0] == 5) $this->orderby( array( 'p.comment_count', 'ASC' ) );
			if ($sort_array[0] == 5) $this->orderby( array( 'p.post_title', 'ASC' ) );

			if ($sort_array[0] == -5) $this->orderby( array( 'p.comment_count', 'DESC' ) );
			if ($sort_array[0] == -5) $this->orderby( array( 'p.post_title', 'ASC' ) );
		}

		if( $sort_array[0] == 6 || $sort_array[0] == -6 ) {
			$this->left_join( $this->prefix . 'postmeta dm', array( 'p.ID = dm.meta_value', 'dm.meta_key = \'_link_to_data\'' ) );
			$this->left_join( $this->prefix . 'posts d', array( 'd.ID = dm.post_id', 'd.post_type = \'discussion\'', 'd.post_status = \'publish\'' ) );
			$this->groupby( 'p.ID' );

			if ($sort_array[0] == 6) $this->orderby( array( 'COUNT(d.ID)', 'ASC' ) );
			if ($sort_array[0] == 6) $this->orderby( array( 'p.post_title', 'ASC' ) );

			if ($sort_array[0] == -6) $this->orderby( array( 'COUNT(d.ID)', 'DESC' ) );
			if ($sort_array[0] == -6) $this->orderby( array( 'p.post_title', 'ASC' ) );
		}

		if( $sort_array[0] == 7 || $sort_array[0] == -7 ) {
			$this->left_join( $this->prefix.'postmeta am', array( 'p.ID = am.meta_value', 'am.meta_key = \'_link_to_data\'' ) );
			$this->left_join( $this->prefix . 'posts a', array( 'a.ID = am.post_id', 'a.post_type = \'application\'', 'a.post_status = \'publish\'' ) );
			$this->groupby( 'p.ID' );

			if ($sort_array[0] == 7) $this->orderby( array( 'MAX(a.post_date_gmt)', 'ASC' ) );
			if ($sort_array[0] == 7) $this->orderby( array( 'p.post_title', 'ASC' ) );

			if ($sort_array[0] == -7) $this->orderby( array( 'MAX(a.post_date_gmt)', 'DESC' ) );
			if ($sort_array[0] == -7) $this->orderby( array( 'p.post_title', 'ASC' ) );
		}

	}

	/**
	 * Takes in meta_key / meta_value pair to be searched
	 *
	 * @param string $meta_key Meta_key to be searched
	 * @param $values
	 * @param bool $like Should meta_key be searched with MySQL syntax "LIKE '%value%'"
	 * @param string $not_null_text
	 *
	 * @internal param array $meta_value Meta_value to be searched
	 *
	 */
	public function search_meta( $meta_key, $values, $like = false , $not_null_text = '') {
		$this->meta[] = array( $meta_key, $values, $like , $not_null_text);
	}
	
	/**
	 * Sets the text part of search
	 *
	 * @param array $words Array of all words to be searched in post title or content
	 */
	public function search_text( $words ) {
		foreach ( $words as $word ) {
			$this->query_words[] = "(
			p.post_content LIKE '%$word%' OR
			p.post_title LIKE '%$word%' OR
			m.meta_value LIKE '%$word%'
		)";
		}
	}
	
	/**
	 * Additional where clauses to query
	 *
	 * @param string $column DB column name
	 * @param string|array $value Value to be searched
	 * @param bool $like Should value  be searched with MySQL syntax "LIKE '%value%'"
	 */
	public function set_where( $column, $value, $like = false ) {
		$this->where[] = array($column, $value, $like);
	}
	
	public function set_where_raw( $clause ) {
		$this->where_raw[] = $clause;
	}

	/**
	 * @param $tax_array
	 * @param bool $require_all
	 * @return void
	 */
	public function search_taxonomy( $tax_array, $require_all = false ) {
		foreach( $tax_array as $k => $a ) {
			end( $a );
			$field = key( $a );
			$this->taxonomies[$k][] = array( 0 => $require_all, $field => $a[ $field ] ) ;
		}
	}

	/**
	 * @param string $field
	 * @return void
	 */
	public function groupby( $field ) {
		$this->groupby[] = $field;
	}

	/**
	 * @param string $table
	 * @param array $on
	 * @return void
	 */
	public function left_join( $table, $on ) {
		$this->leftjoin[] = array( $table, $on );
	}

	/**
	 * Gets count of how many rows the statement would have returned without the LIMIT.
	 *
	 * Uses SQL_CALC_FOUND_ROWS so count is available only after the query has been executed.
	 *
	 * @return bool|int
	 */
	public function get_count() {
		return $this->resultCount;
	}

	/**
	 * @return string
	 */
	public function get_query() {
		if( is_null($this->query) ) $this->build_query();
		return $this->query;
	}
	
	/**
	 * Builds the query
	 */
	public function build_query() {
		$calc = ( $this->calc_rows ) ? 'SQL_CALC_FOUND_ROWS ' : null;
		$source = ( $this->source ) ? ", '{$this->source}' AS source" : null;
		$this->query = "SELECT {$calc}DISTINCT {$this->field}{$source} FROM {$this->prefix}posts p";
		$this->query_body();
	}
	
	private function query_body() {

		$this->set_where('p.post_status', 'publish');
		$where_clauses = array();
	
		$i = 1;

		if ( !empty( $this->taxonomies ) ) {

			$tax_queries = '';

			foreach ( $this->taxonomies as $taxonomy => $tx_groups ) {

				foreach( $tx_groups as $tx ) {

					end( $tx );
					$field = key( $tx );

					if ( $tx[0] == true ) {

						foreach( $tx[ $field ] as $value ) {

							$this->query .= "\nINNER JOIN (
	{$this->prefix}term_relationships r$i, {$this->prefix}term_taxonomy x$i, {$this->prefix}terms t$i
) ON (
	p.ID = r$i.object_id
	AND r$i.term_taxonomy_id = x$i.term_taxonomy_id
	AND x$i.term_id = t$i.term_id
)";

							if ( $field == 'id' ) $tax_queries['and'][] = array( "t$i.term_id", $value );
							if ( $field == 'slug' ) $tax_queries['and'][] = array( "t$i.slug", $value );

							$this->set_where( "x$i.taxonomy", $taxonomy );
							$i++;

						}

					} else {

						$this->query .= "\nINNER JOIN (
	{$this->prefix}term_relationships r$i, {$this->prefix}term_taxonomy x$i, {$this->prefix}terms t$i
) ON (
	p.ID = r$i.object_id
	AND r$i.term_taxonomy_id = x$i.term_taxonomy_id
	AND x$i.term_id = t$i.term_id
)";

						if ( isset( $tx['id'] ) ) $tax_queries['or'][] = array( "t$i.term_id", $tx['id'] );
						if ( isset( $tx['slug'] ) ) $tax_queries['or'][] = array( "t$i.slug", $tx['slug'] );

						$this->set_where( "x$i.taxonomy", $taxonomy );
						$i++;
					}
				}
			}

			if( isset($tax_queries['and']) ) {
				$add_words = array();
				foreach( $tax_queries['and'] as $and ) {
					$add_words[] = "{$and[0]} = '{$and[1]}'";
				}
				$this->set_where_raw( implode( ' AND ', $add_words ) );
			}

			if( isset($tax_queries['or']) ) {
				foreach( $tax_queries['or'] as $or ) {
					$this->set_where( $or[0], $or[1] );
				}
			}
		}

		if ( !empty($this->meta) ) {
			foreach($this->meta as $meta) {

				$this->query .= "\nLEFT JOIN {$this->prefix}postmeta m$i ON p.ID = m$i.post_id";

				$this->set_where( "m$i.meta_key", $meta[0], $meta[2] );

				// Added by O.Kokko 18.1.2012 to remove empty titled results from list
				if (isset($meta[3]) && $meta[3] == 'NOT NULL') {
					$this->set_where_raw( "m$i.meta_value <> ''");
				} else {
					$this->set_where( "m$i.meta_value", $meta[1] );
				}
				/*
				//Old:
				$this->set_where( "m$i.meta_value", $meta[1] );
				*/
				
				$i++;
			}
		}

		if ( $this->leftjoin ) foreach( $this->leftjoin as $leftjoin ) {

			if ( is_array($leftjoin[1]) ) $on = implode( "\n\tAND ", $leftjoin[1] );
			else $on = $leftjoin[1];
			$this->query .= "\nLEFT JOIN {$leftjoin[0]}\n\tON " . $on;
		}

		if ( $this->where ) foreach( $this->where as $where ) {
			
			if ( !is_array( $where[1] ) ) {
				if ( $where[2] === true ) $where_clauses[] = "{$where[0]} LIKE '%{$where[1]}%'";
				else $where_clauses[] = "{$where[0]} = '{$where[1]}'";
			} else {
				
				$where_clause_parts = array();
				foreach( $where[1] as $w ) {
					if ( $where[2] === true ) $where_clause_parts[] = "{$where[0]} LIKE '%$w%'";
					else $where_clause_parts[] = "{$where[0]} = '$w'";
				}
				
				$where_clauses[] = "(\n\t\t" . implode("\n\t\tOR ", $where_clause_parts) . "\n\t)";
				
			}
		}
		
		if ( $this->where_raw ) foreach( $this->where_raw as $where_raw ) {
			$where_clauses[] = $where_raw;
		}
		
		if ( isset( $this->query_words ) ) $this->query .= "\nINNER JOIN {$this->prefix}postmeta m ON p.ID = m.post_id";
		
		if ( !empty( $where_clauses ) ) $this->query .= "\nWHERE \t" . implode( "\n\tAND ", $where_clauses );
		if ( isset( $this->query_words ) ) $this->query .= "\n\tAND (\n\t\t" . implode(" AND\n\t\t", $this->query_words) . "\n\t)";

		if ( $this->groupby ) $this->query .= "\nGROUP BY " . implode( ', ', $this->groupby );

		if ( !empty( $this->order ) ) {
			$this->query .= "\nORDER BY";
			$orders = array();

			foreach ( $this->order as $order ) {
				$orders[] = "\n\t{$order[0]} {$order[1]}";
			}

			$this->query .= implode( ',', $orders );
		}
		
		if ( isset( $this->limit ) ) $this->query .= $this->limit;
	}
	
	/**
	 * Executes the query and returns found post IDs
	 *
	 * @return array|bool $IDs Array of found IDs or false
	 */
	public function execute() {
		
		global $wpdb;
		
		if ( !$wpdb ) return false;
		
//		echo '<pre>' . $this->query . '</pre>';
		
		$results = $wpdb->get_results( $this->query );
		if ( $results ) {

			foreach ( $results as $result ) $IDs[] = (int) $result->ID;

			$this->resultCount = (int) $wpdb->get_var( "SELECT FOUND_ROWS();" );

		}
		
		return ( isset($IDs) && !empty($IDs) ) ? $IDs : false;
		
	}
}

?>