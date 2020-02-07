set search_path=public,so,frange,genetic_code;
select 'drop view ' || schemaname || '.' || viewname || ' cascade;'
from pg_views where schemaname = 'so';

select 'drop view ' || schemaname || '.' || viewname || ' cascade;'
from pg_views where schemaname = 'chado';
