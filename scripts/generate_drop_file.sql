set search_path=public,so,frange,genetic_code;
select 'drop view "' || viewname || '" cascade;'
from pg_views where schemaname = 'so';

select 'drop view "' || viewname || '" cascade;'
from pg_views where schemaname = 'chado';
