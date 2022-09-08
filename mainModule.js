const server = Deno.listen({ port: 8080 });

for await (const conn of server) {
	handle(conn);
}

async function handle(conn) {
	const httpConn = Deno.serveHttp(conn);
	for await (const requestEvent of httpConn) {
		await requestEvent.respondWith(
			new Response('hello world', {
				status: 200,
			})
		);
	}
}
