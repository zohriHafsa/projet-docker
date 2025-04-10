
FROM node:18 AS build

WORKDIR /app
COPY . .
RUN npm install && npm run build --prod

FROM nginx:latest
COPY --from=build /app/dist/frontend /usr/share/nginx/html

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
