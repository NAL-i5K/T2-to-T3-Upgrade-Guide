set search_path=public,so,frange,genetic_code,chado;

select 'drop index ' || schemaname || '.' || indexname || ';'
from pg_indexes where indexname like '%idx%' AND schemaname = 'chado';