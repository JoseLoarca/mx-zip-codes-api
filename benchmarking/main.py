import requests
from database import connection


def get_zip_codes(limit=100):
    """
    Get a list of zip codes in random order
    :return:
    """
    db = connection.connect()
    with db.cursor() as cursor:
        sql = "SELECT zip_code FROM zip_codes ORDER BY RAND() LIMIT %s"
        cursor.execute(sql, (limit,))
        return cursor.fetchall()


def test_api(url, limit):
    """
    Test the API with a list of zip codes
    :param url: API url
    :param limit: total of zip codes to test
    :return:
    """
    zip_codes = get_zip_codes(limit)

    # Set an empty list to store non-cached response times
    non_cached_response_times = []
    # Set an empty list to store cached response times
    cached_response_times = []

    print('\n')
    print('********************************************************')
    print('API URl: {}'.format(url))
    print('This script will retrieve {} zip codes from the database.'.format(limit_input))
    print('Two sets of requests will be made to the API using the retrieved zip codes.')
    print('The first set will only include non-cached results.')
    print('The second set will include cached results.')
    print('********************************************************')
    print('\n')
    print('Starting...')
    print('\n')
    print('Non-cached requests:')

    # Loop through the zip codes for the first time, this will return the response times for non-cached requests and
    # will make the API cache the responses. The next time the loop runs, the API will be cached and should be slower
    for zip_code in zip_codes:
        response = requests.get(url + zip_code['zip_code'])
        response_time = response.elapsed.total_seconds() * 1000
        non_cached_response_times.append(response_time)
        print('Response time for non-cached request for zip code {}: {:.2f} ms'.format(zip_code['zip_code'],
                                                                                       response_time))

    print('Non-cached requests complete.')
    average_non_cached_response_time = sum(non_cached_response_times) / len(non_cached_response_times)
    print('Average response time for non-cached requests: {:.2f} ms'.format(average_non_cached_response_time))
    print('********************************************************')
    print('Cached requests:')

    # Loop through the zip codes again, this time the API should be cached and should be faster
    for zip_code in zip_codes:
        response = requests.get(url + zip_code['zip_code'])
        response_time = response.elapsed.total_seconds() * 1000
        cached_response_times.append(response_time)
        print('Response time for cached request for zip code {}: {:.2f} ms'.format(zip_code['zip_code'], response_time))

    print('Cached requests complete.')
    average_cached_response_time = sum(cached_response_times) / len(cached_response_times)
    print('Average response time for cached requests: {:.2f} ms'.format(average_cached_response_time))

    print('\n')
    print('Improvement of response time: {:.2f}%'.format(
        (average_non_cached_response_time - average_cached_response_time) / average_non_cached_response_time * 100))


# Execute script, remember to clear cache before running script
if __name__ == '__main__':
    url_input = input("Enter the API URL: ")
    limit_input = int(input("Enter number of zip codes to retrieve: "))
    test_api(url_input, limit_input)
