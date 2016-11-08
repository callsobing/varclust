import subprocess as sp
import sys

chromosome = sys.argv[1]
start = str(sys.argv[2])
end = str(sys.argv[3])
job_id = sys.argv[4]
clustering_m = sys.argv[5]

region = " " + chromosome + " " + start + " " + end + " " + job_id
sp.call('sudo mkdir -m 777 /var/www/html/varclust/record/' + job_id + '/', shell=True)

command_get_genotype = '/root/workspace/spark-1.6.2/bin/spark-submit --driver-memory=8g --executor-memory=45g --conf "spark.cores.max=45" --class C4lab.GenotypeGetter --master=spark://server:7077 /var/www/html/varclust/viTFBS-1.0.0.jar /data/' + region + " 2>record/" + job_id + "/error" + " 1>record/" + job_id + "/output"
return_code_genotype = sp.call(command_get_genotype, shell=True)

if return_code_genotype:
    sp.call('touch /var/www/html/varclust/record/' + job_id + '/GENOTYPE_FAIL', shell=True)
else:
    sp.call('touch /var/www/html/varclust/record/' + job_id + '/GENOTYPE_SUCCESS', shell=True)

command_clustering = 'sudo /usr/bin/python3 clusteringAndGenJson.py ' + job_id + ' ' + clustering_m
return_code_clustering = sp.call(command_clustering, shell=True)

if return_code_clustering:
    sp.call('touch /var/www/html/varclust/record/' + job_id + '/CLUSTERING_FAIL', shell=True)
else:
    sp.call('touch /var/www/html/varclust/record/' + job_id + '/CLUSTERING_SUCCESS', shell=True)
